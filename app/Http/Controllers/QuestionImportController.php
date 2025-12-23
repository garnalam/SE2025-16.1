<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Imports\QuestionsImport;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class QuestionImportController extends Controller
{
    // Hiển thị trang Import
    public function create()
    {
        $user = auth()->user();
        $subjects = $user->subjects()->orderBy('name')->get();
        $tags = $user->tags()->orderBy('name')->get();

        return Inertia::render('Questions/Import', [
            'subjects' => $subjects,
            'tags' => $tags,
        ]);
    }

    // Xử lý file
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv',
            'subject_id' => 'required|integer|exists:subjects,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ]);

        $user = auth()->user();
        $subjectId = $request->input('subject_id');
        $tagIds = $request->input('tags', []);

        // Bắt đầu Import
        Excel::import(new QuestionsImport($user, $subjectId, $tagIds), $request->file('file'));

        return redirect()->route('questions.index')->with('success', 'Import câu hỏi thành công!');
    }

    // Cho phép tải file mẫu
    public function downloadTemplate()
    {
        // ➡️ Sửa 1: Dùng storage_path() để lấy file từ thư mục 'storage'
        $path = storage_path('app/public/templates/import_questions_template.xlsx');

        if (!file_exists($path)) {
            // ➡️ Sửa 2: Cập nhật thông báo lỗi cho đúng
            abort(404, 'File mẫu không tìm thấy. Vui lòng kiểm tra storage/app/public/templates/');
        }

        // Dùng helper 'response()->download'
        return response()->download($path, 'File-mau-import-cau-hoi.xlsx');
    }
}
