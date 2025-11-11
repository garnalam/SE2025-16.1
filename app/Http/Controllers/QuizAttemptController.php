<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class QuizAttemptController extends Controller
{
    /**
     * Bắt đầu một lần làm bài (hoặc tiếp tục bài đang dang dở).
     */
    public function start(Post $post)
    {
        Gate::authorize('view', $post->team); // Đảm bảo học sinh ở trong lớp

        // 1. Tìm bài đang dang dở (chưa nộp)
        $attempt = $post->attempts()->firstWhere([
            'user_id' => auth()->id(),
            'completed_at' => null
        ]);

        // 2. Nếu đã nộp bài, chuyển đến trang kết quả
        if (!$attempt) {
            $completedAttempt = $post->attempts()->firstWhere([
                'user_id' => auth()->id(),
            ]);
            if ($completedAttempt) {
                return redirect()->route('quiz.results', $completedAttempt->id);
            }
        }

        // 3. Nếu chưa có bài nào -> Tạo bài mới
        if (!$attempt) {
            $attempt = $post->attempts()->create([
                'user_id' => auth()->id(),
                'started_at' => now(),
                'question_order' => $this->getQuestionOrder($post) // QUAN TRỌNG
            ]);
        }

        // 4. Chuyển đến câu hỏi đầu tiên
        return redirect()->route('quiz.question.show', [
            'attempt' => $attempt->id,
            'questionNumber' => 1
        ]);
    }

    /**
     * Lấy và xáo trộn ID câu hỏi.
     */
    private function getQuestionOrder(Post $post)
    {
        $questionIds = $post->questions()->pluck('id');

        if ($post->shuffle_questions) { // Kiểm tra cờ 'shuffle_questions'
            return $questionIds->shuffle()->values(); // [5, 1, 8, 3]
        }
        return $questionIds; // [1, 3, 5, 8]
    }

    /**
     * Hiển thị một câu hỏi cụ thể.
     */
    public function showQuestion(QuizAttempt $attempt, $questionNumber)
    {
        $this->authorize('view', $attempt); // Policy: Đảm bảo là chủ bài

        $questionIds = $attempt->question_order; // [5, 1, 8, 3]
        $totalQuestions = count($questionIds);

        if ($questionNumber < 1 || $questionNumber > $totalQuestions) {
            abort(404);
        }

        // Lấy ID câu hỏi cho trang này
        $currentQuestionId = $questionIds[$questionNumber - 1];

        // Lấy câu hỏi VÀ các lựa chọn (options)
        $question = Question::with('options')->findOrFail($currentQuestionId);
        // (Nhờ $hidden, 'is_correct' sẽ bị ẩn)

        // Lấy câu trả lời trước đó của học sinh
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

    /**
     * Lưu câu trả lời của học sinh.
     */
    public function saveAnswer(Request $request, QuizAttempt $attempt, $questionNumber)
    {
        $this->authorize('update', $attempt); // Policy: Bài chưa nộp

        $request->validate(['option_id' => 'required|exists:question_options,id']);

        $questionIds = $attempt->question_order;
        $totalQuestions = count($questionIds);
        $currentQuestionId = $questionIds[$questionNumber - 1];

        // Lưu hoặc cập nhật câu trả lời
        $attempt->answers()->updateOrCreate(
            [
                'question_id' => $currentQuestionId,
            ],
            [
                'question_option_id' => $request->option_id
            ]
        );

        // Chuyển tới câu tiếp theo
        if ($questionNumber < $totalQuestions) {
            return redirect()->route('quiz.question.show', [
                'attempt' => $attempt->id,
                'questionNumber' => $questionNumber + 1
            ]);
        }
        
        // Nếu là câu cuối, đến trang xác nhận
        return redirect()->route('quiz.submitPage', $attempt->id);
    }

    /**
     * Hiển thị trang xác nhận nộp bài.
     */
    public function submitPage(QuizAttempt $attempt)
    {
        $this->authorize('update', $attempt); // Vẫn là 'update' vì chưa nộp
        
        // Đếm xem trả lời được bao nhiêu câu
        $answeredCount = $attempt->answers()->count();
        $totalQuestions = count($attempt->question_order);

        return Inertia::render('Quiz/Submit', [
            'attempt' => $attempt,
            'answeredCount' => $answeredCount,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    /**
     * Nộp bài và chấm điểm.
     */
    public function finishAttempt(QuizAttempt $attempt)
    {
        $this->authorize('update', $attempt); // Lần cuối dùng 'update'

        // 1. Lấy ID các câu hỏi
        $questionIds = $attempt->question_order;

        // 2. Lấy TẤT CẢ đáp án ĐÚNG
        // Kết quả: [question_id => correct_option_id]
        $correctOptions = QuestionOption::whereIn('question_id', $questionIds)
            ->where('is_correct', true)
            ->pluck('id', 'question_id');

        // 3. Lấy TẤT CẢ câu trả lời CỦA HỌC SINH
        // Kết quả: [question_id => selected_option_id]
        $studentAnswers = $attempt->answers
            ->pluck('question_option_id', 'question_id');

        // 4. Chấm điểm
        $score = 0;
        foreach ($correctOptions as $qId => $correctOptId) {
            if (isset($studentAnswers[$qId]) && $studentAnswers[$qId] == $correctOptId) {
                $score++;
            }
        }

        // 5. Tính điểm (theo thang 100 hoặc max_points của Post)
        $total = count($questionIds);
        $maxPoints = $attempt->post->max_points ?? 100;
        $finalGrade = ($score / $total) * $maxPoints;

        // 6. Cập nhật kết quả
        $attempt->update([
            'completed_at' => now(),
            'score' => $finalGrade
        ]);

        return redirect()->route('quiz.results', $attempt->id);
    }
    
    /**
     * Hiển thị kết quả.
     */
    public function showResults(QuizAttempt $attempt)
    {
        $this->authorize('view', $attempt); // Policy 'view'
        
        $attempt->load('post:id,topic_id,title,max_points'); // Lấy thông tin post
        
        return Inertia::render('Quiz/Results', [
            'attempt' => $attempt
        ]);
    }
}