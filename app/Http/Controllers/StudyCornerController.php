<?php

namespace App\Http\Controllers;

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

        // 1. Lấy tài liệu: Bao gồm tài liệu của Giáo viên (chung cho lớp) VÀ tài liệu học sinh tự up
        $documents = StudyDocument::where('team_id', $teamId)
            ->where(function($query) use ($userId) {
                $query->where('is_teacher_resource', true)
                      ->orWhere('user_id', $userId);
            })
            ->with(['annotations' => function($q) use ($userId) {
                // Chỉ load vết vẽ của chính user này thôi (không xem vết vẽ của bạn khác)
                $q->where('user_id', $userId);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Lấy danh sách vở ghi (Notebooks)
        $notebooks = StudyNotebook::where('team_id', $teamId)
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        // Trả về View React (Chúng ta sẽ tạo file này ở bước sau)
return Inertia::render('StudySpace/MemoryShards', [
        'teamId' => $teamId,
        'documents' => $documents,
        'notebooks' => $notebooks
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
}