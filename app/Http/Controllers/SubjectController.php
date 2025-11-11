<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubjectController extends Controller
{
    /**
     * Hiển thị trang quản lý Môn học.
     */
    public function index()
    {
        $subjects = auth()->user()->subjects()->orderBy('name')->get();
        
        return Inertia::render('Subjects/Index', [
            'subjects' => $subjects
        ]);
    }

    /**
     * Lưu một Môn học mới.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        auth()->user()->subjects()->create([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Đã tạo Môn học.');
    }

    /**
     * Cập nhật Môn học.
     */
    public function update(Request $request, Subject $subject)
    {
        // Đảm bảo user sở hữu môn học này
        $this->authorize('update', $subject); 

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $subject->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Đã cập nhật Môn học.');
    }

    /**
     * Xóa Môn học.
     */
    public function destroy(Subject $subject)
    {
        // Đảm bảo user sở hữu môn học này
        $this->authorize('delete', $subject);

        // Lưu ý: Migration của chúng ta (`onDelete('set null')`)
        // sẽ tự động gán 'subject_id' của các câu hỏi thành NULL.
        $subject->delete();

        return redirect()->back()->with('success', 'Đã xóa Môn học.');
    }
}