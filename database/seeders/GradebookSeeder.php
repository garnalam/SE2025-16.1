<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Post;
use App\Models\Topic;
use App\Models\Submission;
use App\Models\QuizAttempt;

class GradebookSeeder extends Seeder
{
    public function run(): void
    {
        // ====================================================
        // CẤU HÌNH: ĐÚNG VỚI DỮ LIỆU HIỆN TẠI CỦA BẠN
        // ====================================================
        $teamId = 2;      // <--- QUAN TRỌNG: ID lớp "test" của bạn là 2
        $teacherId = 1;   // ID giáo viên của bạn
        // ====================================================

        $faker = Faker::create('vi_VN'); 
        $password = Hash::make('password');

        DB::transaction(function () use ($teamId, $teacherId, $faker, $password) {
            
            $this->command->info("Đang tạo dữ liệu cho Team ID: $teamId...");

            // 1. Tạo Topic (Đã fix lỗi thiếu user_id)
            $topic = Topic::create([
                'team_id' => $teamId,
                'user_id' => $teacherId, // Fix lỗi database strict mode
                'name' => 'Chương: Dữ liệu giả lập (Seeder)',
            ]);

            // 2. TẠO 50 HỌC SINH MỚI
            $this->command->info('Creating 50 students...');
            $studentIds = [];

            for ($i = 0; $i < 50; $i++) {
                // Tạo User
                $student = User::create([
                    'name' => $faker->name,
                    'email' => $faker->unique()->userName . '@student.test', // Email giả để không trùng
                    'password' => $password,
                    'role' => 'student',
                    'email_verified_at' => now(),
                ]);
                
                $studentIds[] = $student->id;

                // Gán vào Lớp (Quan trọng: role = student)
                DB::table('team_user')->insert([
                    'team_id' => $teamId,
                    'user_id' => $student->id,
                    'role' => 'student', // <-- Bắt buộc phải là 'student'
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 3. TẠO BÀI TẬP & QUIZ
            // (Chỉ tạo nếu chưa có nhiều, để tránh rác DB)
            $existingAssignments = Post::where('team_id', $teamId)->where('post_type', 'assignment')->count();
            
            if ($existingAssignments < 5) {
                $this->command->info('Creating Assignments & Quizzes...');
                // Tạo bài tập
                for ($i = 1; $i <= 5; $i++) {
                    $dueDate = $i <= 3 ? Carbon::now()->subDays(rand(1, 5)) : Carbon::now()->addDays(rand(1, 5));
                    Post::create([
                        'user_id' => $teacherId,
                        'team_id' => $teamId,
                        'topic_id' => $topic->id,
                        'post_type' => 'assignment',
                        'title' => "Bài tập Seeder $i",
                        'max_points' => 100,
                        'due_date' => $dueDate,
                    ]);
                }
                // Tạo Quiz
                for ($i = 1; $i <= 5; $i++) {
                    Post::create([
                        'user_id' => $teacherId,
                        'team_id' => $teamId,
                        'topic_id' => $topic->id,
                        'post_type' => 'quiz',
                        'title' => "Quiz Seeder $i",
                        'max_points' => 10,
                    ]);
                }
            }

            // Lấy lại danh sách bài tập/quiz để chấm điểm
            $assignments = Post::where('team_id', $teamId)->where('post_type', 'assignment')->get();
            $quizzes = Post::where('team_id', $teamId)->where('post_type', 'quiz')->get();

            // 4. CHẤM ĐIỂM (LÀM BÀI)
            $this->command->info('Submitting grades...');
            foreach ($studentIds as $studentId) {
                // Chấm bài tập
                foreach ($assignments as $assign) {
                    if (rand(1, 100) > 80) continue; // 20% không làm bài
                    Submission::create([
                        'post_id' => $assign->id,
                        'user_id' => $studentId,
                        'grade' => rand(50, 100),
                        'content' => 'Seeder content',
                        'submitted_at' => now(),
                    ]);
                }
                // Làm quiz
                foreach ($quizzes as $quiz) {
                    if (rand(1, 100) > 80) continue;
                    QuizAttempt::create([
                        'post_id' => $quiz->id,
                        'user_id' => $studentId,
                        'score' => rand(40, 100) / 10,
                        'started_at' => now(),
                        'completed_at' => now(),
                        'question_order' => json_encode([]),
                    ]);
                }
            }
        });

        $this->command->info("✅ XONG! Đã thêm 50 học sinh vào Team ID $teamId.");
    }
}