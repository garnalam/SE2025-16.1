<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Flashcard;
use App\Models\FlashcardSet;
use App\Models\StudyDocument;
use App\Models\StudyNotebook;
use App\Models\DocumentAnnotation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class StudyCornerController extends Controller
{
    /**
     * Hiển thị trang chính của Tab "Ghi chú / Memory Shards"
     */
public function index($teamId)
    {
        $userId = auth()->id();

        // 1. Lấy Tài liệu (Giữ nguyên code cũ)
        $documents = StudyDocument::where('team_id', $teamId)
            ->where(function($query) use ($userId) {
                $query->where('is_teacher_resource', true)
                      ->orWhere('user_id', $userId);
            })
            ->with(['annotations' => function($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Lấy Notebooks (Giữ nguyên code cũ)
        $notebooks = StudyNotebook::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // 3. [MỚI] Lấy danh sách Flashcard Sets
        $flashcardSets = FlashcardSet::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->withCount('cards') // Đếm số thẻ
            ->latest()
            ->get();

        return Inertia::render('StudySpace/MemoryShards', [
            'teamId' => $teamId,
            'documents' => $documents,
            'notebooks' => $notebooks,
            'flashcardSets' => $flashcardSets // <--- Truyền thêm biến này
        ]);
    }

    /**
     * Xử lý Upload tài liệu
     */
    public function uploadDocument(Request $request, $teamId)
    {
        $request->validate([
            'file' => 'required|mimes:pdf,jpg,png,jpeg,doc,docx|max:20480', // Max 20MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        
        // Lưu vào storage public để frontend truy cập được
        $path = $file->storeAs("study_documents/{$teamId}", time() . '_' . $originalName, 'public');

        StudyDocument::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'title' => $originalName,
            'file_path' => '/storage/' . $path,
            'file_type' => $extension,
            'is_teacher_resource' => false // Mặc định là học sinh tự up
        ]);

        return redirect()->back()->with('success', 'Đã tải lên tài liệu thành công!');
    }

    /**
     * Lưu nội dung Notebook (Text/Excel)
     */
public function storeNotebook(Request $request, $teamId)
{
    $data = $request->validate([
        'id' => 'nullable|integer',
        'title' => 'required|string|max:255',
        'content' => 'nullable', 
        'type' => 'required|in:notebook,spreadsheet'
    ]);

    StudyNotebook::updateOrCreate(
        ['id' => $request->id], 
        [
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'title' => $data['title'],
            
            // --- SỬA DÒNG NÀY ---
            // Dùng toán tử ?? null để nếu không có content thì lưu là null
            'content' => $data['content'] ?? null, 
            // --------------------
            
            'type' => $data['type']
        ]
    );

    return redirect()->back();
}

    /**
     * Lưu vết vẽ (Annotation)
     */
    public function saveAnnotation(Request $request, $documentId)
    {
        $data = $request->validate([
            'page_number' => 'required|integer',
            'data' => 'required|json' // Dữ liệu nét vẽ dạng JSON string
        ]);

        DocumentAnnotation::updateOrCreate(
            [
                'study_document_id' => $documentId,
                'user_id' => auth()->id(),
                'page_number' => $data['page_number']
            ],
            [
                'data' => json_decode($data['data']) // Convert về mảng để lưu
            ]
        );

        return redirect()->back(); // Trả về yên lặng để người dùng vẽ tiếp
    }

    // --- [MỚI] CÁC HÀM XỬ LÝ FLASHCARD ---

    // 1. Tạo bộ thẻ mới (Ví dụ: "Từ vựng Unit 1")
    public function storeFlashcardSet(Request $request, $teamId)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string'
        ]);

        FlashcardSet::create([
            'team_id' => $teamId,
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'description' => $data['description'] ?? '',
            'color' => $data['color'] ?? '#2dd4bf'
        ]);

        return redirect()->back();
    }

// Thêm $teamId vào trước $setId để khớp với thứ tự trên URL
public function storeFlashcard(Request $request, $teamId, $setId)
{
    $data = $request->validate([
        'front' => 'required|string',
        'back' => 'required|string',
    ]);

    Flashcard::create([
        'flashcard_set_id' => $setId, // Lúc này $setId sẽ nhận đúng giá trị 1
        'front_content' => $data['front'],
        'back_content' => $data['back']
    ]);

    return redirect()->back();
}
    
    // 3. API lấy danh sách thẻ để học (Khi bấm vào bộ thẻ)
    public function getFlashcards($setId)
    {
        // Kiểm tra quyền sở hữu nếu cần
        $cards = Flashcard::where('flashcard_set_id', $setId)->get();
        return response()->json($cards);
    }

public function generateFlashcardsAi(Request $request, $teamId, $setId)
{
    // 1. Validate
    $request->validate([
        'document_id' => 'required|exists:study_documents,id',
        'quantity' => 'required|integer|min:1|max:20', // Giới hạn 20 thẻ để AI xử lý nhanh
    ]);

    $document = StudyDocument::find($request->document_id);
    $quantity = $request->quantity;

    // 2. Chuẩn bị nội dung gửi cho AI
    // Mặc định lấy Tiêu đề làm ngữ cảnh
    $contextContent = "Chủ đề tài liệu: " . $document->title;

    // Thử đọc nội dung file nếu là dạng văn bản đơn giản
    try {
        if (Storage::exists($document->file_path)) {
            $extension = pathinfo($document->file_path, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['txt', 'md', 'json', 'csv'])) {
                $fileContent = Storage::get($document->file_path);
                // Cắt ngắn nếu quá dài để tránh lỗi token limit (lấy 10.000 ký tự đầu)
                $contextContent .= "\n\nNội dung chi tiết: " . substr($fileContent, 0, 10000);
            } else {
                $contextContent .= "\n(Lưu ý: Đây là file $extension, hãy tạo câu hỏi dựa trên chủ đề của tiêu đề trên)";
            }
        }
    } catch (\Exception $e) {
        Log::error("Lỗi đọc file: " . $e->getMessage());
    }

    // 3. Cấu hình Prompt cho Gemini
    $prompt = "Bạn là một trợ lý học tập AI. Hãy tạo danh sách $quantity flashcard (Thẻ ghi nhớ) dựa trên nội dung sau:\n\n" . 
              $contextContent . 
              "\n\n--- YÊU CẦU QUAN TRỌNG ---\n" .
              "1. Trả về kết quả dưới dạng JSON thuần (Raw JSON), không có markdown ```json ```.\n" .
              "2. Cấu trúc mảng object: [{ \"front\": \"Câu hỏi hoặc Từ vựng\", \"back\": \"Đáp án hoặc Giải nghĩa\" }].\n" .
              "3. Ngôn ngữ: Tiếng Việt (hoặc theo ngôn ngữ của tài liệu).\n" .
              "4. Nội dung ngắn gọn, súc tích.";

    // 4. Gọi API Gemini
    $apiKey = env('GEMINI_API_KEY');
    // Lưu ý: Dùng URL chuẩn của model 1.5 Flash (Model 2.5 trong env của bạn có thể chưa public hoặc sai tên, tôi dùng bản ổn định 1.5)
    // Nếu bạn chắc chắn về model 2.5, hãy dùng env('GEMINI_API_URL')
    $apiUrl = env('GEMINI_API_URL') . "?key={$apiKey}";

    try {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7, // Độ sáng tạo vừa phải
                'maxOutputTokens' => 2000,
            ]
        ]);

        if ($response->failed()) {
            Log::error('Gemini API Error: ' . $response->body());
            return back()->withErrors(['msg' => 'Lỗi kết nối AI: ' . $response->status()]);
        }

        $responseData = $response->json();
        
        // 5. Parse kết quả
        // Gemini trả về text trong: candidates[0].content.parts[0].text
        $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Làm sạch chuỗi JSON (đôi khi AI trả về ```json ... ```)
        $aiText = str_replace(['```json', '```'], '', $aiText);
        $cardsData = json_decode($aiText, true);

        if (!is_array($cardsData)) {
            Log::error('AI JSON Parse Error: ' . $aiText);
            return back()->withErrors(['msg' => 'AI trả về dữ liệu không đúng định dạng. Hãy thử lại.']);
        }

        // 6. Lưu vào Database
        foreach ($cardsData as $card) {
            Flashcard::create([
                'flashcard_set_id' => $setId,
                'front_content' => $card['front'] ?? 'Lỗi',
                'back_content' => $card['back'] ?? 'Không có nội dung',
            ]);
        }

        return redirect()->back()->with('success', "Đã tạo thành công " . count($cardsData) . " thẻ từ AI!");

    } catch (\Exception $e) {
        Log::error("AI Generate Exception: " . $e->getMessage());
        return back()->withErrors(['msg' => 'Đã xảy ra lỗi khi gọi AI.']);
    }
}
}