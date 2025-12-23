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
        Log::info("===========================================");
        Log::info("=== BẮT ĐẦU JOB CHẤM AI (Submission ID: {$this->submission->id}) ===");
        
        $submission = $this->submission->load(['post', 'files']); 
        $post = $submission->post;
        $attachments = $post->attachments()->get(); 

        // --- GIAI ĐOẠN 1: XỬ LÝ ĐỀ BÀI (GIÁO VIÊN) ---
        $teacherText = $post->content ?? "";
        $teacherFileUris = [];

        Log::info("--- GĐ1: Xử lý file giáo viên (SL: {$attachments->count()}) ---");
        foreach ($attachments as $attachment) {
            $path = null;
            if (Storage::exists($attachment->path)) $path = Storage::path($attachment->path);
            elseif (Storage::disk('public')->exists($attachment->path)) $path = Storage::disk('public')->path($attachment->path);

            if (!$path) {
                Log::error("GV: Không tìm thấy file vật lý: " . $attachment->path);
                continue;
            }

            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($extension === 'docx') {
                $content = $this->readWordFile($path);
                $teacherText .= "\n[FILE ĐÍNH KÈM GIÁO VIÊN]:\n" . $content;
                Log::info("GV: Đã đọc Word. Độ dài: " . strlen($content));
            } elseif (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
                $uri = $this->uploadFileToGemini($path, $this->getMimeType($extension));
                if ($uri) $teacherFileUris[] = $uri;
            }
        }

        // --- GIAI ĐOẠN 2: XỬ LÝ BÀI LÀM (HỌC SINH) ---
        $studentText = $submission->content ?? ""; 
        $studentFileUris = [];
        
        Log::info("--- GĐ2: Xử lý file học sinh (SL: {$submission->files->count()}) ---");

        if ($submission->files->count() > 0) {
            foreach ($submission->files as $file) {
                $path = Storage::path($file->file_path);
                
                if (!file_exists($path)) {
                    Log::error("HS: File không tồn tại: " . $path);
                    continue;
                }

                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                Log::info("HS: Đang xử lý file đuôi .$extension tại $path");

                // >>> FIX QUAN TRỌNG: Đọc file docx của học sinh <<<
                if ($extension === 'docx') {
                    $extracted = $this->readWordFile($path);
                    if (trim($extracted) !== "") {
                        $studentText .= "\n\n[FILE BÀI LÀM HỌC SINH]:\n" . $extracted;
                        Log::info("HS: Đã trích xuất text từ Word. Độ dài: " . strlen($extracted));
                    } else {
                        Log::warning("HS: File Word rỗng hoặc không đọc được text.");
                    }
                } 
                elseif (in_array($extension, ['pdf', 'jpg', 'jpeg', 'png', 'webp'])) {
                    $uri = $this->uploadFileToGemini($path, $this->getMimeType($extension));
                    if ($uri) {
                        $studentFileUris[] = $uri;
                        Log::info("HS: Upload Gemini thành công URI: $uri");
                    }
                } else {
                    Log::warning("HS: Định dạng không hỗ trợ xử lý ($extension)");
                }
            }
        }

        // Check text cuối cùng trước khi gửi
        if (empty(trim($studentText)) && empty($studentFileUris)) {
            Log::error("!!! CẢNH BÁO: Bài làm học sinh hoàn toàn trống sau khi xử lý !!!");
        } else {
            Log::info("HS: Tổng text gửi đi: " . strlen($studentText) . " ký tự.");
        }

        // --- GIAI ĐOẠN 3: GỬI REQUEST ---
        $prompt = <<<EOT
        Bạn là giáo viên chấm bài.
        
        === ĐỀ BÀI ===
        Điểm tối đa: {$post->max_points}
        Nội dung đề:
        $teacherText

        === BÀI LÀM CỦA HỌC SINH ===
        $studentText

        YÊU CẦU:
        1. Đọc kỹ đề bài và bài làm.
        2. Nếu bài làm trống hoặc không liên quan, hãy cho 0 điểm và nhắc nhở.
        3. Trả về JSON duy nhất: { "grade": số_điểm, "feedback": "nhận xét chi tiết" }
        EOT;

        try {
            $apiKey = env('GEMINI_API_KEY');
            $url = env('GEMINI_API_URL') . "?key=" . $apiKey;

            $parts = [];
            $parts[] = ['text' => $prompt];

            foreach ($teacherFileUris as $uri) {
                $parts[] = ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $uri]];
            }
            foreach ($studentFileUris as $uri) {
                $parts[] = ['file_data' => ['mime_type' => 'application/pdf', 'file_uri' => $uri]];
            }
            
            Log::info("--- GĐ3: Đang gửi request sang Gemini... ---");

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
            Log::info("Gemini Reply: " . substr(json_encode($data), 0, 200) . "..."); 

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception("Cấu trúc phản hồi Gemini không đúng.");
            }

            $responseText = $data['candidates'][0]['content']['parts'][0]['text'];
            $result = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                 // Fallback nếu Gemini trả về markdown json code block
                 if (preg_match('/\{.*\}/s', $responseText, $matches)) {
                    $result = json_decode($matches[0], true);
                 }
            }

            $this->submission->update([
                'ai_suggested_grade' => $result['grade'] ?? 0,
                'ai_suggested_feedback' => $result['feedback'] ?? "Lỗi đọc phản hồi AI.",
            ]);
            
            Log::info("=== HOÀN TẤT JOB ===");

        } catch (\Exception $e) {
            Log::error("AI Grading Error: " . $e->getMessage());
        }
    }

    // --- CÁC HÀM PHỤ TRỢ ---
private function readWordFile($path)
    {
        try {
            // Tắt các warning xml lib cũ để tránh log rác
            libxml_use_internal_errors(true);
            
            $phpWord = IOFactory::load($path);
            $text = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . " ";
                    }
                    // Handle bảng biểu cơ bản nếu cần
                    else if (method_exists($element, 'getRows')) {
                        foreach ($element->getRows() as $row) {
                            foreach ($row->getCells() as $cell) {
                                foreach ($cell->getElements() as $cellElement) {
                                    if (method_exists($cellElement, 'getText')) {
                                        $text .= $cellElement->getText() . " ";
                                    }
                                }
                            }
                            $text .= "\n";
                        }
                    }
                }
                $text .= "\n";
            }
            return trim($text);
        } catch (\Exception $e) {
            Log::error("Lỗi đọc file Word ($path): " . $e->getMessage());
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