<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * Hiển thị trang quản lý Thẻ.
     */
    public function index()
    {
        $tags = auth()->user()->tags()->orderBy('name')->get();
        
        return Inertia::render('Tags/Index', [
            'tags' => $tags
        ]);
    }

    /**
     * Lưu một Thẻ mới.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // 'firstOrCreate' để tránh tạo thẻ trùng lặp
        auth()->user()->tags()->firstOrCreate([
            'name' => $request->name,
        ]);

        return redirect()->back()->with('success', 'Đã tạo Thẻ.');
    }

    /**
     * Cập nhật Thẻ.
     */
    public function update(Request $request, Tag $tag)
    {
        $this->authorize('update', $tag);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tag->update(['name' => $request->name]);

        return redirect()->back()->with('success', 'Đã cập nhật Thẻ.');
    }

    /**
     * Xóa Thẻ.
     */
    public function destroy(Tag $tag)
    {
        $this->authorize('delete', $tag);
        
        // Bảng pivot `question_tag` sẽ tự động xóa các liên kết.
        $tag->delete();

        return redirect()->back()->with('success', 'Đã xóa Thẻ.');
    }
}