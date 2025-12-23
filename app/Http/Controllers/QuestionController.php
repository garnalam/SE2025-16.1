<?php
namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Cần thiết cho transaction
use Inertia\Inertia; // <-- THÊM DÒNG NÀY
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http; // <-- THÊM ĐỂ GỌI API GEMINI
use Illuminate\Support\Facades\Log;  // <-- THÊM ĐỂ LOG LỖI
use Smalot\PdfParser\Parser; // <-- THÊM
use PhpOffice\PhpWord\IOFactory; // <-- THÊM
use Illuminate\Support\Facades\Storage; // <--- Thêm dòng này
class QuestionController extends Controller
{
    public function __construct()
    {
        // Áp dụng policy cho tất cả các method (trừ index, create, store)
        $this->authorizeResource(Question::class, 'question');
    }

    // Hiển thị tất cả câu hỏi trong kho CÁ NHÂN
    public function index(Request $request) // <-- Thêm Request $request
    {
        $user = auth()->user();

        // Lấy tất cả Môn học và Thẻ để gửi cho bộ lọc
        $subjects = $user->subjects()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        // Bắt đầu query
        $query = $user->questions()->with(['options', 'subject', 'tags']);

        // 1. Lọc theo Subject (Môn học)
        if ($request->filled('subject')) {
            $query->where('subject_id', $request->input('subject'));
        }

        // 2. Lọc theo Tags (Thẻ)
        if ($request->filled('tags')) {
            $tagIds = $request->input('tags'); // Giả sử đây là mảng các ID
            // Tìm câu hỏi có TẤT CẢ các thẻ được chọn
            $query->whereHas('tags', function ($q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            }, '=', count($tagIds));
        }
        
        // 3. Lọc theo Text (Tìm kiếm)
        if ($request->filled('search')) {
            $query->where('question_text', 'like', '%' . $request->input('search') . '%');
        }

        // Lấy kết quả và phân trang
        $questions = $query->latest()->paginate(20)
                         ->withQueryString(); // <-- Giữ lại query param khi chuyển trang
        $questions->getCollection()->transform(function ($question) {
            $question->options->each(function ($option) {
                // Làm cho trường 'is_correct' hiển thị lại
                $option->makeVisible(['is_correct']);
            });
            $question->append('image_url');
            return $question;
        });
        return Inertia::render('Questions/Index', [
            'questions' => $questions,
            'subjects' => $subjects, // <-- Gửi cho bộ lọc
            'tags' => $tags,         // <-- Gửi cho bộ lọc
            'filters' => $request->only(['subject', 'tags', 'search']), // <-- Gửi lại filter
        ]);
    }

    // Hiển thị form tạo câu hỏi
    public function create()
    {
        // Lấy tất cả Môn học và Thẻ của user đang đăng nhập
        $user = auth()->user();
        $subjects = $user->subjects()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        return Inertia::render('Questions/Create', [
            'subjects' => $subjects, // <-- Gửi subjects
            'tags' => $tags,         // <-- Gửi tags
        ]);
    }

    // Lưu câu hỏi mới
    public function store(Request $request)
    {
        $request->validate([
            'question_text' => 'required|string|min:5',
            'options' => [
                'required',
                'array',
                'min:2',
                function ($attribute, $value, $fail) {
                    // Lấy ra danh sách text của các options
                    $texts = array_map(function ($item) {
                        return trim($item['text']); // Xóa khoảng trắng thừa
                    }, $value);
                    
                    // So sánh số lượng gốc với số lượng sau khi loại bỏ trùng lặp
                    if (count($texts) !== count(array_unique($texts))) {
                        $fail('Các lựa chọn đáp án không được trùng nhau.');
                    }
                },
            ],
            'options.*.text' => 'required|string',
            'correct_option' => 'required|integer',
            
            // Validate dữ liệu mới (Subject)
            // 'subject_id' có thể là số (ID đã có) hoặc chuỗi "new:Tên Môn Mới"
            'subject_id' => 'required|integer|exists:subjects,id', // 1. Chỉ chấp nhận ID
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id', // 2. Yêu cầu mọi thứ trong mảng 'tags' phải là ID
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // <-- THÊM DÒNG NÀY
        ]);

        $user = auth()->user();
        $tagIds = $request->input('tags', []);
        $subjectId = $request->input('subject_id');
        // Dùng transaction để đảm bảo lưu đồng bộ
        $question = DB::transaction(function () use ($request, $user, $subjectId, $tagIds) {
            
            $imagePath = null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('question_images', 'public');
            }

            $question = $user->questions()->create([
                'question_text' => $request->question_text,
                'subject_id' => $subjectId, // <-- Lưu subject_id
                'type' => 'single_choice',
                'image_path' => $imagePath, // <--- THÊM DÒNG NÀY QUAN TRỌNG NHẤT
            ]);

            // Gắn các thẻ (Tags) vào câu hỏi
            $question->tags()->sync($tagIds);

            // Lưu các lựa chọn (Options)
            foreach ($request->options as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $request->correct_option)
                ]);
            }
            
            return $question;
        });

        return redirect()->route('questions.index')->with('success', 'Tạo câu hỏi thành công!');
    }

    // Hiển thị form chỉnh sửa
    public function edit(Question $question)
    {
        // Tải các quan hệ của câu hỏi này (Môn học và Thẻ của nó)
        $question->load(['subject', 'tags', 'options']);

        $question->append('image_url');

        $question->options->each(function ($option) {
            $option->makeVisible(['is_correct']);
        });
        
        // Lấy tất cả Môn học và Thẻ của giáo viên (để lấp đầy dropdown)
        $user = auth()->user();
        $subjects = $user->subjects()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        return Inertia::render('Questions/Edit', [
            'question' => $question,
            'subjects' => $subjects,
            'tags' => $tags,
        ]);
    }

    // Cập nhật câu hỏi
    public function update(Request $request, Question $question)
    {
        $request->validate([
            'question_text' => 'required|string|min:5',
            'options' => [
                'required',
                'array',
                'min:2',
                function ($attribute, $value, $fail) {
                    // Lấy ra danh sách text của các options
                    $texts = array_map(function ($item) {
                        return trim($item['text']); // Xóa khoảng trắng thừa
                    }, $value);
                    
                    // So sánh số lượng gốc với số lượng sau khi loại bỏ trùng lặp
                    if (count($texts) !== count(array_unique($texts))) {
                        $fail('Các lựa chọn đáp án không được trùng nhau.');
                    }
                },
            ],
            'options.*.text' => 'required|string',
            'correct_option' => 'required|integer',
            'subject_id' => 'required|integer|exists:subjects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $user = auth()->user();
        $subjectId = $request->input('subject_id');
        $tagIds = $request->input('tags', []);

        DB::transaction(function () use ($request, $question, $subjectId, $tagIds) {
            
            // ===== THÊM KHỐI NÀY (LOGIC XỬ LÝ ẢNH CŨ/MỚI) =====
            $imagePath = $question->image_path; // Giữ ảnh cũ
            if ($request->hasFile('image')) {
                // 1. Xóa ảnh cũ nếu có
                if ($question->image_path) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($question->image_path);
                }
                // 2. Tải ảnh mới lên
                $imagePath = $request->file('image')->store('question_images', 'public');
            }
            // ============================================

            $question->update([
                'question_text' => $request->question_text,
                'subject_id' => $subjectId, // <-- Cập nhật subject_id
                'image_path' => $imagePath // Đảm bảo lưu ảnh
            ]);

            // Cập nhật (Sync) các thẻ
            $question->tags()->sync($tagIds);

            // 4. CẬP NHẬT OPTIONS (KHÔNG XÓA MẤT GỐC)
            $newOptionsData = $request->options; // Dữ liệu từ form gửi lên
            $existingOptions = $question->options; // Dữ liệu đang có trong DB

            // Duyệt qua từng option gửi lên để cập nhật
            foreach ($newOptionsData as $index => $data) {
                // Nếu option cũ vẫn còn ở vị trí này -> Update
                if (isset($existingOptions[$index])) {
                    $existingOptions[$index]->update([
                        'option_text' => $data['text'],
                        'is_correct' => ($index == $request->correct_option)
                    ]);
                } else {
                    // Nếu là option mới thêm vào -> Create
                    $question->options()->create([
                        'option_text' => $data['text'],
                        'is_correct' => ($index == $request->correct_option)
                    ]);
                }
            }

            // Nếu số lượng option mới ít hơn cũ (ví dụ: giảm từ 4 xuống 3 đáp án)
            // Thì mới xóa các option thừa ở cuối đi
            if ($existingOptions->count() > count($newOptionsData)) {
                $idsToDelete = $existingOptions->slice(count($newOptionsData))->pluck('id');
                // Chỉ xóa những đáp án thừa, không ảnh hưởng đáp án đầu
                \App\Models\QuestionOption::destroy($idsToDelete);
            }
        });

        return redirect()->route('questions.index')->with('success', 'Cập nhật câu hỏi thành công!');
    }

    // Xóa câu hỏi
    public function destroy(Question $question)
    {
        $question->delete();
        return redirect()->route('questions.index')->with('success', 'Xóa câu hỏi thành công!');
    }
    // --- [NEW] 1. Hàm xử lý upload file và gọi AI ---
    public function generateAiQuestions(Request $request)
    {
        $request->validate([
            'documents' => 'required|array|min:1|max:5', // Cho phép tối đa 5 file cùng lúc
            'documents.*' => 'file|mimes:pdf,doc,docx,txt|max:10240', // Validate từng file
            'number_of_questions' => 'required|integer|min:1|max:20',
            'custom_instructions' => 'nullable|string|max:500', // <-- Thêm validate cho yêu cầu riêng
        ]);

        try {
            // 1. Đọc và ghép nội dung từ TẤT CẢ các file
            $fullText = "";
            foreach ($request->file('documents') as $index => $file) {
                $fileText = $this->extractTextFromFile($file);
                $fullText .= "\n--- TÀI LIỆU " . ($index + 1) . " ---\n" . $fileText;
            }

            if (strlen($fullText) < 50) {
                return response()->json(['error' => 'Nội dung các file quá ngắn hoặc không đọc được.'], 400);
            }

            // 2. Chuẩn bị Prompt với yêu cầu riêng (Custom Instructions)
            $userInstruction = $request->custom_instructions 
                ? "LƯU Ý QUAN TRỌNG TỪ GIÁO VIÊN: " . $request->custom_instructions 
                : "";

            $prompt = "Bạn là trợ lý soạn đề thi. Hãy tạo " . $request->number_of_questions . " câu hỏi trắc nghiệm.
            
            $userInstruction
            
            Dựa trên tổng hợp các văn bản sau:
            " . substr($fullText, 0, 50000) . " 
            
            YÊU CẦU ĐỊNH DẠNG JSON (Tuyệt đối chỉ trả về JSON, không có text thừa):
            [
                {
                    \"question_text\": \"Câu hỏi?\",
                    \"options\": [
                        {\"text\": \"A\", \"is_correct\": false},
                        {\"text\": \"B\", \"is_correct\": true},
                        {\"text\": \"C\", \"is_correct\": false},
                        {\"text\": \"D\", \"is_correct\": false}
                    ]
                }
            ]";

            // 3. Gọi Gemini (Giữ nguyên logic cũ)
            $apiKey = env('GEMINI_API_KEY');
            $apiUrl = env('GEMINI_API_URL') . '?key=' . $apiKey;

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($apiUrl, [
                    'contents' => [['parts' => [['text' => $prompt]]]],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->failed()) {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json(['error' => 'Lỗi kết nối AI.'], 500);
            }

            $responseData = $response->json();
            $aiText = $responseData['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $aiText = str_replace(['```json', '```'], '', $aiText); // Clean markdown
            
            $questions = json_decode($aiText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'AI trả về lỗi định dạng.'], 500);
            }

            return response()->json(['questions' => $questions]);

        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    // --- [NEW] 2. Hàm lưu danh sách câu hỏi (Sau khi giáo viên duyệt) ---
    public function storeBulk(Request $request)
    {
        $request->validate([
            'questions' => 'required|array',
            'questions.*.question_text' => 'required|string',
            'questions.*.options' => 'required|array',
            'subject_id' => 'required|exists:subjects,id',
            'tags' => 'nullable|array'
        ]);

        $user = auth()->user();
        $subjectId = $request->subject_id;
        $tagIds = $request->tags ?? [];

        DB::transaction(function () use ($request, $user, $subjectId, $tagIds) {
            foreach ($request->questions as $qData) {
                // Tạo câu hỏi
                $question = $user->questions()->create([
                    'question_text' => $qData['question_text'],
                    'subject_id' => $subjectId,
                    'type' => 'single_choice'
                ]);

                // Gắn tags
                if (!empty($tagIds)) {
                    $question->tags()->sync($tagIds);
                }

                // Tạo options
                foreach ($qData['options'] as $opt) {
                    $question->options()->create([
                        'option_text' => $opt['text'],
                        'is_correct' => $opt['is_correct'] ?? false
                    ]);
                }
            }
        });

        return redirect()->route('questions.index')->with('success', 'Đã lưu ' . count($request->questions) . ' câu hỏi vào ngân hàng.');
    }

    // --- [NEW] 3. Hàm phụ trợ đọc file ---
    private function extractTextFromFile($file)
    {
        $extension = $file->getClientOriginalExtension();
        $text = '';

        if ($extension === 'pdf') {
            $parser = new Parser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();
        } elseif (in_array($extension, ['doc', 'docx'])) {
            $phpWord = IOFactory::load($file->getPathname());
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
        } elseif ($extension === 'txt') {
            $text = file_get_contents($file->getPathname());
        }

        return $text;
    }
    
}