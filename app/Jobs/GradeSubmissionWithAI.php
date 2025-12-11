<?php

namespace App\Jobs;

use App\Events\AiAnalysisCompleted; // <--- Import Event
use App\Models\Submission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;

class GradeSubmissionWithAI implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $submission;

    public function __construct(Submission $submission)
    {
        $this->submission = $submission;
    }

    public function handle(): void
    {
        Log::info("=== BẮT ĐẦU CHẤM AI ===");
        
        // Load dữ liệu
        $submission = $this->submission->load(['post', 'files']); 
        $post = $submission->post;

        // CHECK 1: Kiểm tra quan hệ Attachments
        // Nếu Post model chưa khai báo hàm attachments(), cái này sẽ lỗi hoặc rỗng
        $attachments = $post->attachments()->get(); 
        Log::info("Số lượng file đính kèm của giáo viên: " . $attachments->count());

        // --- GIAI ĐOẠN 1: XỬ LÝ ĐỀ BÀI ---
        $teacherText = $post->content ?? "";
        $teacherFileUris = [];

        foreach ($attachments as $attachment) {
            Log::info("Đang xử lý file ID: {$attachment->id} - Path: {$attachment->path}");

            // CHECK 2: Tìm đường dẫn thực tế
            $path = null;
            if (Storage::exists($attachment->path)) {
                $path = Storage::path($attachment->path);
                Log::info("-> Tìm thấy ở ổ LOCAL: " . $path);
            } 
            elseif (Storage::disk('public')->exists($attachment->path)) {
                $path = Storage::disk('public')->path($attachment->path);
                Log::info("-> Tìm thấy ở ổ PUBLIC: " . $path);
            } else {
                Log::error("-> KHÔNG TÌM THẤY file vật lý!");
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            Log::info("-> Đuôi file: " . $extension);

            if ($extension === 'docx') {
                $teacherText .= "\n[NỘI DUNG FILE ĐÍNH KÈM]:\n" . $this->readWordFile($path);
                Log::info("-> Đã đọc text từ Word");
            } 
            elseif (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
                // Upload
                $uri = $this->uploadFileToGemini($path, $this->getMimeType($extension));
                if ($uri) {
                    $teacherFileUris[] = $uri;
                    Log::info("-> Upload Gemini thành công. URI: " . $uri);
                } else {
                    Log::error("-> Upload Gemini THẤT BẠI");
                }
            } else {
                Log::info("-> File không được hỗ trợ xử lý.");
            }
        }

        // Kiểm tra kết quả gom được
        Log::info("Tổng kết Teacher URIs: " . json_encode($teacherFileUris));

        // --- GIAI ĐOẠN 2: XỬ LÝ BÀI LÀM (Giữ nguyên logic cũ, chỉ thêm log) ---
        $studentText = $submission->content ?? ""; 
        $studentFileUris = [];

        if ($submission->files->count() > 0) {
            foreach ($submission->files as $file) {
                // Logic tìm path file học sinh
                $path = Storage::path($file->file_path); 
                Log::info("Học sinh file: " . $path);
                
                if (!file_exists($path)) {
                    Log::error("-> File học sinh không tồn tại!");
                    continue;
                }

                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                if (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
                    $uri = $this->uploadFileToGemini($path, $this->getMimeType($extension));
                    if ($uri) $studentFileUris[] = $uri;
                }
            }
        }

        // --- GIAI ĐOẠN 3: GỬI REQUEST ---
        $prompt = <<<EOT
        Bạn là trợ lý giáo viên. Chấm điểm bài làm sau.
        
        === ĐỀ BÀI (QUAN TRỌNG) ===
        Thang điểm: {$post->max_points}
        Text: "$teacherText"
        (Xem thêm file đính kèm nếu có)

        === BÀI LÀM ===
        Text: "$studentText"
        (Xem thêm file đính kèm nếu có)

        YÊU CẦU: Trả về JSON { "grade": số, "feedback": "..." }
        EOT;

        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = env('GEMINI_API_URL') . "?key=" . $apiKey;

            $parts = [];
            $parts[] = ['text' => $prompt];

            // Gắn file giáo viên
            foreach ($teacherFileUris as $uri) {
                $parts[] = ['text' => "FILE ĐỀ BÀI:"];
                $parts[] = ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $uri]];
            }

            // Gắn file học sinh
            foreach ($studentFileUris as $uri) {
                $parts[] = ['text' => "FILE BÀI LÀM:"];
                $parts[] = ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $uri]];
            }
            
            Log::info("Đang gửi request sang Gemini...");

            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [['parts' => $parts]],
                    'generationConfig' => [
                        'temperature' => 0.4,
                        'responseMimeType' => 'application/json'
                    ]
                ]);

            if ($response->failed()) {
                throw new \Exception("Gemini API Error: " . $response->body());
            }

            $data = $response->json();
            Log::info("Gemini trả về: " . json_encode($data)); // Log kết quả

            $result = json_decode($data['candidates'][0]['content']['parts'][0]['text'], true);

            $this->submission->update([
                'ai_suggested_grade' => $result['grade'],
                'ai_suggested_feedback' => $result['feedback'],
            ]);
            
            Log::info("=== KẾT THÚC THÀNH CÔNG ===");

        } catch (\Exception $e) {
            Log::error("AI Grading Error: " . $e->getMessage());
        }
    }

    // --- CÁC HÀM PHỤ TRỢ ---
    private function readWordFile($path)
    {
        try {
            $phpWord = IOFactory::load($path);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }
            return $text;
        } catch (\Exception $e) {
            return "";
        }
    }

    private function uploadFileToGemini($filePath, $mimeType)
    {
        $apiKey = env('GEMINI_API_KEY');
        $uploadUrl = "https://generativelanguage.googleapis.com/upload/v1beta/files?key=" . $apiKey;

        $response = Http::withHeaders([
            'X-Goog-Upload-Command' => 'start, upload, finalize',
            'X-Goog-Upload-Header-Content-Length' => filesize($filePath),
            'X-Goog-Upload-Mime-Type' => $mimeType,
            'Content-Type' => 'application/json',
        ])->withBody(
            file_get_contents($filePath), $mimeType
        )->post($uploadUrl);

        if ($response->successful()) {
            $data = $response->json();
            return $data['file']['uri'];
        }
        
        Log::error("Gemini Upload Failed: " . $response->body());
        return null;
    }

    private function getMimeType($extension) {
        $mimes = [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
        ];
        return $mimes[$extension] ?? 'application/pdf';
    }
}