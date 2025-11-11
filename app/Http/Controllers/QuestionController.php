<?php
namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Cần thiết cho transaction
use Inertia\Inertia; // <-- THÊM DÒNG NÀY
use App\Http\Controllers\Controller;

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
            'options' => 'required|array|min:2',
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
                'type' => 'single_choice'
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
        $question->load('subject', 'tags');
        
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
            'options' => 'required|array|min:2',
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
            ]);

            // Cập nhật (Sync) các thẻ
            $question->tags()->sync($tagIds);

            // Xóa các lựa chọn cũ
            $question->options()->delete();

            // Thêm các lựa chọn mới
            foreach ($request->options as $index => $optionData) {
                $question->options()->create([
                    'option_text' => $optionData['text'],
                    'is_correct' => ($index == $request->correct_option)
                ]);
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
    
}