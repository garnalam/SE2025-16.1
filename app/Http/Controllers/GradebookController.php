<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GradebookService;
use Inertia\Inertia;
use App\Models\Team;

class GradebookController extends Controller
{
    protected $gradebookService;

    public function __construct(GradebookService $gradebookService)
    {
        $this->gradebookService = $gradebookService;
    }

    public function index(Request $request, $teamId)
    {
        $user = $request->user();
        
        // 1. Lấy đối tượng Team từ ID trước
        $team = Team::findOrFail($teamId);

        // Kiểm tra quyền truy cập lớp học (Bảo mật)
        if (!$team->hasUser($user) && !$user->ownsTeam($team)) {
            abort(403);
        }

        // 2. Logic phân quyền Teacher/Student
        // [FIX BUG]: Sử dụng $user->ownsTeam($team) thay vì $user->ownsTeam($teamId)
        if ($user->role === 'teacher' || $user->ownsTeam($team)) {
            // VIEW CỦA GIÁO VIÊN
            $data = $this->gradebookService->getClassGradebook($teamId);
            return Inertia::render('Gradebook/Show', [
                'team' => $team,
                'isTeacher' => true,
                'rawPosts' => $data['posts'],
                'gradebook' => $data['students_data'],
                'currentWeights' => $data['weights'],
            ]);
        } 
        else {
            // VIEW CỦA HỌC SINH
            $data = $this->gradebookService->getStudentGradebook($teamId, $user->id);
            return Inertia::render('Gradebook/Show', [
                'team' => $team,
                'isTeacher' => false,
                'studentData' => $data
            ]);
        }
    }
    public function updateSettings(Request $request, $teamId)
    {
        // Check quyền Teacher
        if ($request->user()->role !== 'teacher') abort(403);

        $request->validate([
            'weights.attendance' => 'required|numeric',
            'weights.regular' => 'required|numeric',
            'weights.midterm' => 'required|numeric',
            'weights.final' => 'required|numeric',
            // midterm_id và final_id có thể null
        ]);

        $this->gradebookService->updateSettings($teamId, $request->all());

        return back()->with('flash', ['banner' => 'Cập nhật bảng điểm thành công!']);
    }
}