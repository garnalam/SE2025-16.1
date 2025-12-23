<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Carbon\Carbon; // Thêm Carbon để lấy giờ

class QuizAttemptController extends Controller
{
    /**
     * Bắt đầu làm bài
     */
    public function start(Post $post)
    {
        Gate::authorize('view', $post->team);

        $attempt = $post->attempts()->firstWhere([
            'user_id' => auth()->id(),
            'completed_at' => null
        ]);

        if (!$attempt) {
            $completedAttempt = $post->attempts()->firstWhere([
                'user_id' => auth()->id(),
            ]);
            if ($completedAttempt) {
                return redirect()->route('quiz.results', $completedAttempt->id);
            }
        }

        if (!$attempt) {
            $attempt = $post->attempts()->create([
                'user_id' => auth()->id(),
                'started_at' => now(),
                'question_order' => $this->getQuestionOrder($post)
            ]);
        }

        return redirect()->route('quiz.question.show', [
            'attempt' => $attempt->id,
            'questionNumber' => 1
        ]);
    }

    private function getQuestionOrder(Post $post)
    {
        $questionIds = $post->questions()->pluck('id');
        if ($post->shuffle_questions) {
            return $questionIds->shuffle()->values();
        }
        return $questionIds;
    }

    public function showQuestion(QuizAttempt $attempt, $questionNumber)
    {
        $this->authorize('view', $attempt);

        $attempt->load('post');
        $questionIds = $attempt->question_order;
        $totalQuestions = count($questionIds);

        if ($questionNumber < 1 || $questionNumber > $totalQuestions) {
            abort(404);
        }

        $currentQuestionId = $questionIds[$questionNumber - 1];
        $question = Question::with('options')->findOrFail($currentQuestionId);

        $previousAnswer = $attempt->answers()
            ->where('question_id', $currentQuestionId)
            ->first();

        return Inertia::render('Quiz/Attempt', [
            'attempt' => $attempt,
            'question' => $question,
            'questionNumber' => (int)$questionNumber,
            'totalQuestions' => $totalQuestions,
            'previousAnswerOptionId' => $previousAnswer?->question_option_id
        ]);
    }

    public function saveAnswer(Request $request, QuizAttempt $attempt, $questionNumber)
    {
        $this->authorize('update', $attempt);
        $request->validate(['option_id' => 'required|exists:question_options,id']);

        $questionIds = $attempt->question_order;
        $totalQuestions = count($questionIds);
        $currentQuestionId = $questionIds[$questionNumber - 1];

        $attempt->answers()->updateOrCreate(
            ['question_id' => $currentQuestionId],
            ['question_option_id' => $request->option_id]
        );

        if ($questionNumber < $totalQuestions) {
            return redirect()->route('quiz.question.show', [
                'attempt' => $attempt->id,
                'questionNumber' => $questionNumber + 1
            ]);
        }
        
        return redirect()->route('quiz.submitPage', $attempt->id);
    }

    public function submitPage(QuizAttempt $attempt)
    {
        $this->authorize('update', $attempt);
        $answeredCount = $attempt->answers()->count();
        $totalQuestions = count($attempt->question_order);

        return Inertia::render('Quiz/Submit', [
            'attempt' => $attempt,
            'answeredCount' => $answeredCount,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    /**
     * Nộp bài thủ công (Học sinh tự bấm nộp)
     */
    public function finishAttempt(QuizAttempt $attempt)
    {
        $this->authorize('update', $attempt);
        $this->calculateScore($attempt); // Gọi hàm chấm điểm chung
        return redirect()->route('quiz.results', $attempt->id);
    }

    /**
     * API: Ghi nhận vi phạm & Tự động nộp nếu quá 3 lần
     */
    public function logViolation(Request $request, QuizAttempt $attempt)
    {

        // Check quyền sở hữu và trạng thái bài thi
        if (auth()->id() !== $attempt->user_id || $attempt->completed_at) {
            return response()->json(['status' => 'error'], 403);
        }
        if (!$attempt->post->is_proctored) {
            return response()->json(['status' => 'ignored']);
        }

        // Tăng vi phạm
        $currentCount = $attempt->violation_count + 1;
        
        // Ghi log
        $logs = $attempt->proctoring_logs ?? [];
        $logs[] = [
            'time' => Carbon::now()->format('H:i:s d/m/Y'),
            'type' => $request->input('type', 'unknown'),
            'message' => 'Rời màn hình thi'
        ];

        $attempt->update([
            'violation_count' => $currentCount,
            'proctoring_logs' => $logs
        ]);

        // Nếu quá 3 lần -> CƯỠNG CHẾ NỘP BÀI
        if ($currentCount >= 3) {
            $this->calculateScore($attempt); // Chấm điểm ngay lập tức
            
            return response()->json([
                'status' => 'terminated',
                'message' => 'Bạn đã vi phạm quy chế quá 3 lần. Bài thi đã tự động nộp.',
                'redirect_url' => route('quiz.results', $attempt->id)
            ]);
        }

        return response()->json([
            'status' => 'warning',
            'violation_count' => $currentCount
        ]);
    }

    /**
     * Hàm chấm điểm nội bộ (Dùng chung)
     */
    private function calculateScore(QuizAttempt $attempt)
    {
        if ($attempt->completed_at) return; // Không chấm lại nếu đã xong

        $questionIds = $attempt->question_order;
        
        // Lấy đáp án đúng
        $correctOptions = QuestionOption::whereIn('question_id', $questionIds)
            ->where('is_correct', true)
            ->pluck('id', 'question_id');

        // Lấy bài làm
        $studentAnswers = $attempt->answers()->pluck('question_option_id', 'question_id');

        $score = 0;
        foreach ($correctOptions as $qId => $correctOptId) {
            if (isset($studentAnswers[$qId]) && $studentAnswers[$qId] == $correctOptId) {
                $score++;
            }
        }

        $total = count($questionIds);
        $maxPoints = $attempt->post->max_points ?? 100;
        $finalGrade = ($total > 0) ? round(($score / $total) * $maxPoints, 2) : 0;

        $attempt->update([
            'completed_at' => now(),
            'score' => $finalGrade
        ]);
    }

    public function showResults(QuizAttempt $attempt)
    {
        $this->authorize('view', $attempt);
        $attempt->load('post:id,topic_id,title,max_points');
        return Inertia::render('Quiz/Results', ['attempt' => $attempt]);
    }
}