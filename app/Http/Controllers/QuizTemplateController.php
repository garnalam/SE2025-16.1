<?php
namespace App\Http\Controllers;

use App\Models\QuizTemplate;
use Illuminate\Http\Request;

class QuizTemplateController extends Controller
{
    /**
     * Lưu một Cấu hình Mẫu mới.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'settings' => 'required|array',
            'settings.subject_id' => 'required|integer|exists:subjects,id',
            'settings.tags' => 'nullable|array',
            'settings.tags.*' => 'integer|exists:tags,id',
            'settings.count' => 'required|integer|min:1',
            'settings.shuffle' => 'required|boolean',
            'settings.points' => 'required|numeric|min:0.1',
        ]);

        $template = auth()->user()->quizTemplates()->create($data);

        // Trả về template (để Vue cập nhật danh sách)
        return redirect()->back()->with('success', 'Đã lưu mẫu cấu hình.');
    }

    /**
     * Xóa một Cấu hình Mẫu.
     */
    public function destroy(QuizTemplate $quizTemplate)
    {
        // Kiểm tra Policy (chỉ chủ sở hữu được xóa)
        $this->authorize('delete', $quizTemplate);

        $quizTemplate->delete();

        return redirect()->back()->with('success', 'Đã xóa mẫu cấu hình.');
    }
}