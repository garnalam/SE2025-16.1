<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY');
        $this->apiUrl = env('GEMINI_API_URL');
    }

    public function generateContent($messages)
    {
        // 1. Kiểm tra cấu hình
        if (!$this->apiKey || !$this->apiUrl) {
            Log::error('>>> GEMINI DEBUG: Thiếu API Key hoặc URL trong .env');
            return "Lỗi cấu hình Server.";
        }

        // 2. Chuẩn bị dữ liệu gửi đi
        $contents = array_map(function ($msg) {
            return [
                'role' => ($msg['role'] === 'user') ? 'user' : 'model',
                'parts' => [['text' => $msg['content']]]
            ];
        }, $messages);

        // --- GHI LOG DỮ LIỆU GỬI ĐI ---
        Log::info('>>> GEMINI REQUEST (Gửi đi):', [
            'url' => $this->apiUrl,
            'payload' => $contents
        ]);

        try {
            // 3. Gọi API (Bỏ qua xác thực SSL để chạy Localhost ngon hơn)
            $response = Http::withoutVerifying()
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => $contents,
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2000,
                    ]
                ]);

            // --- GHI LOG KẾT QUẢ TRẢ VỀ ---
            Log::info('>>> GEMINI RESPONSE STATUS: ' . $response->status());
            Log::info('>>> GEMINI RESPONSE BODY:', $response->json() ?? ['raw' => $response->body()]);

            if ($response->failed()) {
                Log::error('>>> GEMINI ERROR: API trả về lỗi.', ['body' => $response->body()]);
                return "Lỗi từ Google: " . $response->status() . " - " . $response->body();
            }

            $data = $response->json();
            return $data['candidates'][0]['content']['parts'][0]['text'] ?? 'AI không phản hồi nội dung.';

        } catch (\Exception $e) {
            Log::error('>>> GEMINI EXCEPTION (Lỗi mã nguồn): ' . $e->getMessage());
            return "Lỗi hệ thống: " . $e->getMessage();
        }
    }
}