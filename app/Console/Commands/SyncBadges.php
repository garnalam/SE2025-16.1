<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Badge;
use App\Services\GamificationService;

class SyncBadges extends Command
{
    protected $signature = 'badges:sync';
    protected $description = 'Đồng bộ danh sách huy hiệu từ Code vào Database';

    public function handle()
    {
        $this->info('Đang bắt đầu đồng bộ huy hiệu...');

        // Lấy danh sách từ Service
        $badgeClasses = GamificationService::getBadges();

        foreach ($badgeClasses as $class) {
            $instance = new $class();

            // Cập nhật hoặc Tạo mới vào Database
            Badge::updateOrCreate(
                ['name' => $instance->name()], // Tìm theo tên
                [
                    'description' => $instance->description(),
                    'icon_path' => $instance->icon(),
                ]
            );

            $this->info("✔ Đã đồng bộ: " . $instance->name());
        }

        $this->info('Hoàn tất đồng bộ!');
    }
}