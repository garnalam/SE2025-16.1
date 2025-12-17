<?php

namespace App\Http\Controllers;
use App\Models\Post; // <--- THÊM DÒNG NÀY
use App\Models\AiMessage;
use App\Models\Attachment;
use App\Models\AiSession;
use App\Models\StudyDocument;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Question;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Smalot\PdfParser\Parser;

class AiStudyController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    // ==========================================
    // 1. TRANG KHO TÀI LIỆU (DOCUMENTS)
    // ==========================================
   public function indexDocuments()
    {
        $user = Auth::user();
        
        // 1. Lấy tài liệu cá nhân (User tự upload)
        $personalDocs = StudyDocument::where('user_id', $user->id)
            ->select('id', 'title', 'created_at', 'extracted_content')
            ->get()
            ->map(function ($doc) {
                $doc->source_type = 'personal'; 
                return $doc;
            });

        // 2. Lấy tài liệu từ Lớp học (File đính kèm trong bài giảng)
        // Chỉ lấy file PDF đã được AI đọc (extracted_content không null)
        $classDocs = Attachment::whereHasMorph('attachable', [Post::class], function ($query) use ($user) {
                // Lọc theo lớp hiện tại của user
                $query->where('team_id', $user->current_team_id);
            })
            ->whereNotNull('extracted_content') 
            ->select('id', 'original_name as title', 'created_at', 'extracted_content')
            ->latest()
            ->get()
            ->map(function ($doc) {
                $doc->source_type = 'class'; // Đánh dấu là tài liệu lớp
                return $doc;
            });

        // 3. Gộp 2 danh sách lại
        $allDocuments = $personalDocs->merge($classDocs)->sortByDesc('created_at')->values();

        return Inertia::render('StudySpace/Documents', [
            'documents' => $allDocuments
        ]);
    }

    public function uploadDocument(Request $request)
    {
        $request->validate(['file' => 'required|mimes:pdf,txt|max:10240']);

        $file = $request->file('file');
        $text = "";

        // Parse Text
        if ($file->getClientOriginalExtension() === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();
        } else {
            $text = file_get_contents($file->getPathname());
        }

        StudyDocument::create([
            'user_id' => Auth::id(),
            'team_id' => null, // Mặc định upload cá nhân (muốn upload cho lớp thì cần check role giáo viên)
            'title' => $file->getClientOriginalName(),
            'file_path' => $file->store('study_docs'),
            'extracted_content' => $text
        ]);

        return redirect()->back();
    }

    public function chatWithDocument(Request $request)
    {
        $question = $request->input('message');
        $docId = $request->input('document_id');
        // Ở bước indexDocuments ta đã gộp chung, nhưng frontend gửi về chỉ có ID.
        // Ta cần tìm xem ID đó thuộc bảng nào.
        
        // Cách xử lý thông minh: Vì ID có thể trùng giữa 2 bảng, 
        // ở frontend (Bước 4 bên dưới) ta sẽ gửi thêm 'source_type'.
        
        $sourceType = $request->input('source_type', 'personal'); 
        $history = $request->input('history', []);

        $context = "";
        $docTitle = "";

        if ($docId) {
            if ($sourceType === 'personal') {
                $doc = StudyDocument::find($docId);
            } else {
                $doc = Attachment::find($docId);
            }

            if ($doc) {
                $docTitle = $doc->title ?? $doc->original_name;
                $content = $doc->extracted_content;
                
                $context = "--- TÀI LIỆU ĐANG THAM CHIẾU: {$docTitle} ---\n" . 
                           substr($content, 0, 30000) . 
                           "\n--- HẾT TÀI LIỆU ---\n\n";
            }
        }

        // ... Phần gọi Gemini giữ nguyên như cũ ...
        $systemPrompt = "Bạn là trợ lý học tập AI. Hãy trả lời dựa trên tài liệu: $docTitle.";
        
        $geminiMessages = [
            ['role' => 'user', 'content' => $systemPrompt . "\n" . $context]
        ];

        foreach ($history as $msg) {
            $geminiMessages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'content' => $msg['content']
            ];
        }
        $geminiMessages[] = ['role' => 'user', 'content' => $question];

        $answer = $this->gemini->generateContent($geminiMessages);

        return response()->json(['answer' => $answer]);
    }

    // ==========================================
    // 2. TRANG SỬA LỖI SAI (MISTAKES)
    // ==========================================
    public function indexMistakes()
    {
        // Lấy TOÀN BỘ các câu làm sai của học sinh từ trước đến nay
        $wrongAnswers = QuizAnswer::whereHas('quizAttempt', function($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereHas('option', function($q) {
                $q->where('is_correct', 0); // Chỉ lấy câu sai
            })
            ->with(['question.subject', 'question.options', 'option']) // Eager load để lấy dữ liệu
            ->latest()
            ->get()
            ->map(function ($ans) {
                return [
                    'id' => $ans->id,
                    'question_text' => $ans->question->question_text,
                    'student_choice' => $ans->option->option_text,
                    'correct_choice' => $ans->question->options->where('is_correct', 1)->first()->option_text ?? 'N/A',
                    'subject' => $ans->question->subject->name ?? 'Chung',
                    'date' => $ans->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('StudySpace/Mistakes', [
            'mistakes' => $wrongAnswers
        ]);
    }

    public function chatWithMistake(Request $request)
    {
        $question = $request->input('message');
        $mistakeId = $request->input('mistake_id'); // Có thể null
        $history = $request->input('history', []);

        $systemPrompt = "Bạn là gia sư AI kiên nhẫn.";
        $context = "";

        if ($mistakeId) {
            $ans = QuizAnswer::with(['question', 'option'])->find($mistakeId);
            if ($ans) {
                $correct = $ans->question->options->where('is_correct', 1)->first()->option_text ?? 'N/A';
                $context = "--- LỖI SAI CẦN GIẢI ĐÁP ---\n" .
                           "Câu hỏi: {$ans->question->question_text}\n" .
                           "Học sinh chọn sai: {$ans->option->option_text}\n" .
                           "Đáp án đúng: {$correct}\n" . 
                           "--- HẾT THÔNG TIN ---\n";
            }
        }

        $geminiMessages = [['role' => 'user', 'content' => $systemPrompt . "\n" . $context]];

        foreach ($history as $msg) {
            $geminiMessages[] = [
                'role' => $msg['role'] === 'user' ? 'user' : 'model',
                'content' => $msg['content']
            ];
        }

        $geminiMessages[] = ['role' => 'user', 'content' => $question];

        $answer = $this->gemini->generateContent($geminiMessages);

        return response()->json(['answer' => $answer]);
    }
}