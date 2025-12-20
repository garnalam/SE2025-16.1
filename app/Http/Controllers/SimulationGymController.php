<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\Question;
use App\Models\GymSession;
use App\Models\GymResponse;
use App\Models\UserMistake;
use App\Models\Team; 
use App\Services\GeminiService;
use Illuminate\Support\Facades\DB;

class SimulationGymController extends Controller
{
    /**
     * Màn hình chính của Gym (Dashboard)
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // 1. Nhận team_id từ URL
        $teamId = $request->input('team_id');
        $currentTeam = $teamId ? Team::find($teamId) : null;

        $sessionQuery = GymSession::where('user_id', $user->id);
        $mistakeQuery = UserMistake::where('user_id', $user->id);

        // 2. Lọc theo Lớp học
        if ($teamId) {
            // Lọc lỗi sai thuộc về câu hỏi trong Lớp này
            $mistakeQuery->whereHas('question', function($q) use ($teamId) {
                $q->whereHas('posts', function($p) use ($teamId) {
                    $p->where('team_id', $teamId);
                });
            });
            
            // [FIXED] Lọc Session theo team_id (thay vì subject_id)
            $sessionQuery->where('team_id', $teamId); 
        }

        $stats = [
            'total_sessions' => $sessionQuery->count(),
            'mistakes_count' => $mistakeQuery->sum('fail_count'),
            'win_rate' => $this->calculateWinRate($user->id, $teamId),
            'recent_history' => $sessionQuery
                                ->latest()
                                ->take(5)
                                ->get()
                                ->map(function($s) {
                                    return [
                                        'mode' => strtoupper($s->mode),
                                        'score' => $s->correct_count . '/' . $s->total_questions,
                                        'xp' => $s->xp_earned,
                                        'date' => $s->created_at->diffForHumans(),
                                    ];
                                })
        ];

        return Inertia::render('StudySpace/Gym', [
            'stats' => $stats,
            'current_team' => $currentTeam 
        ]);
    }

    /**
     * API: Bắt đầu phiên tập luyện
     */
    public function startSession(Request $request)
    {
        $mode = $request->input('mode', 'quick');
        $teamId = $request->input('team_id'); 
        $user = Auth::user();
        $questions = collect();

        $questionQuery = Question::with('options');

        // [QUAN TRỌNG] Lọc câu hỏi theo Lớp
        if ($teamId) {
            $questionQuery->whereHas('posts', function($query) use ($teamId) {
                $query->where('team_id', $teamId);
            });
        }

        // Chọn câu hỏi
        if ($mode === 'quick') {
            $questions = $questionQuery->inRandomOrder()->take(10)->get();
        } 
        elseif ($mode === 'mistake') {
            $mistakeQuery = UserMistake::where('user_id', $user->id)->orderByDesc('fail_count');

            if ($teamId) {
                $mistakeQuery->whereHas('question', function($q) use ($teamId) {
                    $q->whereHas('posts', fn($p) => $p->where('team_id', $teamId));
                });
            }

            $mistakeIds = $mistakeQuery->pluck('question_id');
            
            if ($mistakeIds->isEmpty()) {
                $questions = $questionQuery->inRandomOrder()->take(10)->get();
            } else {
                $questions = Question::with('options')->whereIn('id', $mistakeIds)->inRandomOrder()->take(10)->get();
            }
        }
        elseif ($mode === 'survival') {
            $questions = $questionQuery->inRandomOrder()->take(30)->get();
        }

        if ($questions->isEmpty()) {
            $msg = $teamId ? 'Lớp học này chưa có câu hỏi nào để luyện tập.' : 'Hệ thống chưa có câu hỏi nào.';
            return response()->json(['message' => $msg], 404);
        }

        // [FIXED] Lưu team_id vào Database (đã đổi tên cột)
        $session = GymSession::create([
            'user_id' => $user->id,
            'mode' => $mode,
            'team_id' => $teamId, // Sử dụng cột team_id
            'total_questions' => $questions->count(),
        ]);

        $questionsPayload = $questions->map(function($q) {
            return [
                'id' => $q->id,
                'question_text' => $q->question_text,
                'options' => $q->options->map(function($opt) {
                    return [
                        'id' => $opt->id, 
                        'text' => $opt->option_text 
                    ];
                })->shuffle()->values(),
            ];
        });

        return response()->json([
            'session_id' => $session->id,
            'questions' => $questionsPayload
        ]);
    }

    public function submitAnswer(Request $request)
    {
        $request->validate([
            'session_id' => 'required',
            'question_id' => 'required',
            'selected_option_id' => 'required'
        ]);

        $question = Question::with('options')->find($request->question_id);
        $correctOption = $question->options->where('is_correct', 1)->first();
        $isCorrect = (int)$request->selected_option_id === (int)$correctOption->id;

        GymResponse::create([
            'gym_session_id' => $request->session_id,
            'question_id' => $request->question_id,
            'selected_answer' => $request->selected_option_id,
            'is_correct' => $isCorrect,
            'time_taken_seconds' => $request->time_taken ?? 0
        ]);

        return response()->json([
            'is_correct' => $isCorrect,
            'correct_option_id' => $correctOption->id
        ]);
    }

    public function finishSession(Request $request, GeminiService $gemini)
    {
        $session = GymSession::with(['responses.question.options'])->findOrFail($request->session_id);
        $user = Auth::user();
        
        $responses = $session->responses;
        $correctCount = $responses->where('is_correct', true)->count();
        $totalQuestions = $session->total_questions;
        
        $xpBase = $correctCount * 10;
        $xpBonus = ($correctCount == $totalQuestions && $totalQuestions > 0) ? 50 : 0;
        $totalXp = $xpBase + $xpBonus;

        $session->update([
            'completed_at' => now(),
            'correct_count' => $correctCount,
            'xp_earned' => $totalXp
        ]);

        if (isset($user->xp)) $user->increment('xp', $totalXp);

        $aiPromptData = [];
        $mistakeDetails = [];

        foreach ($responses as $resp) {
            $q = $resp->question;
            $selectedOpt = $q->options->where('id', $resp->selected_answer)->first();
            $correctOpt = $q->options->where('is_correct', 1)->first();

            $mistakeDetails[] = [
                'question_text' => $q->question_text,
                'is_correct' => $resp->is_correct,
                'your_answer' => $selectedOpt ? $selectedOpt->option_text : 'Bỏ qua',
                'correct_answer' => $correctOpt ? $correctOpt->option_text : 'N/A',
            ];

            if (!$resp->is_correct) {
                UserMistake::updateOrCreate(
                    ['user_id' => $user->id, 'question_id' => $q->id],
                    ['fail_count' => DB::raw('fail_count + 1')]
                );
                $aiPromptData[] = [
                    'cau_hoi' => $q->question_text,
                    'chon_sai' => $selectedOpt ? $selectedOpt->option_text : 'Không chọn',
                    'dap_an_dung' => $correctOpt ? $correctOpt->option_text : 'N/A'
                ];
            } else {
                UserMistake::where('user_id', $user->id)->where('question_id', $q->id)->decrement('fail_count');
            }
        }

        $aiAnalysis = "Bạn làm rất tốt! Không có lỗi sai đáng kể.";
        
        if (!empty($aiPromptData)) {
            try {
                // [FIXED] Lấy tên lớp từ cột team_id
                $context = "";
                if ($session->team_id) {
                    $team = Team::find($session->team_id);
                    if ($team) $context = "trong lớp học: " . $team->name;
                }

                $prompt = "Học sinh vừa làm bài tập $context và sai các câu sau. Hãy phân tích ngắn gọn lý do và đưa ra lời khuyên ôn tập:\n" . json_encode($aiPromptData, JSON_UNESCAPED_UNICODE);
                
                $aiAnalysis = $gemini->generateContent([['role' => 'user', 'content' => $prompt]]);
            } catch (\Exception $e) {
                $aiAnalysis = "Hệ thống AI đang bận. Hãy xem lại chi tiết đáp án bên dưới.";
            }
        }

        return response()->json([
            'summary' => [
                'correct' => $correctCount,
                'total' => $totalQuestions,
                'xp_earned' => $totalXp,
                'rank' => $this->getRank($correctCount, $totalQuestions),
            ],
            'details' => $mistakeDetails,
            'ai_analysis' => $aiAnalysis
        ]);
    }

    private function calculateWinRate($userId, $teamId = null) {
        $query = GymResponse::whereHas('session', function($q) use ($userId, $teamId) {
            $q->where('user_id', $userId);
            if ($teamId) {
                $q->where('team_id', $teamId); // [FIXED] Lọc theo team_id
            }
        });
        
        $total = $query->count();
        if ($total == 0) return 0;
        
        $correct = $query->where('is_correct', true)->count();
        return round(($correct / $total) * 100);
    }

    private function getRank($correct, $total) {
        if ($total == 0) return 'F';
        $percent = ($correct / $total) * 100;
        if ($percent == 100) return 'S';
        if ($percent >= 80) return 'A';
        if ($percent >= 50) return 'B';
        return 'F';
    }
}