<?php
// app/Http/Controllers/PollVoteController.php

namespace App\Http\Controllers;

use App\Models\PollOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

class PollVoteController extends Controller
{
    /**
     * Lưu một phiếu bầu (vote) mới.
     */
    public function store(Request $request, PollOption $pollOption)
    {
        // 1. Lấy thông tin Post và Team từ Option
        $post = $pollOption->post;
        $team = $post->team;

        // 2. Phân quyền: Phải là thành viên của lớp
        // Gate::authorize('addTeamMember', $team);

        if (! $request->user()->belongsToTeam($team)) {
            abort(403, 'Bạn không phải là thành viên của lớp học này.');
        }

        // 3. (QUAN TRỌNG) Kiểm tra xem user đã vote cho poll NÀY chưa
        // Lấy tất cả các ID lựa chọn của poll này
        $optionIds = $post->pollOptions()->pluck('id');
        $userId = $request->user()->id;

        $hasVoted = DB::table('poll_votes')
                        ->where('user_id', $userId)
                        ->whereIn('poll_option_id', $optionIds)
                        ->exists();
        
        // 4. Nếu chưa vote, thì tiến hành vote
        if (! $hasVoted) {
            $pollOption->votes()->create([
                'user_id' => $userId,
            ]);
        }

        // 5. Quay lại (Dù vote rồi hay chưa, cứ quay lại)
        return back(303);
    }
}