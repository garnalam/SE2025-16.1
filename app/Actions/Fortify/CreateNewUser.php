<?php

namespace App\Actions\Fortify;

use App\Models\Team; // <-- THÊM DÒNG NÀY
use App\Models\User;
use Illuminate\Support\Facades\DB; // <-- THÊM DÒNG NÀY
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            
            // Validation cho role
            'role' => ['required', 'string', Rule::in(['student', 'teacher'])], 

            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        // --- SỬA TỪ ĐÂY ---
        return DB::transaction(function () use ($input) {
            return tap(User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
                
                // Thêm 'role' vào lúc tạo User
                'role' => $input['role'], 
            ]), function (User $user) {
                
                // --- LOGIC ĐIỀU KIỆN QUAN TRỌNG ---
                // Chỉ tạo Team nếu role là 'teacher' VÀ dự án có bật tính năng Teams
                // if ($user->role === 'teacher' && Jetstream::hasTeamFeatures()) {
                //     $this->createTeam($user);
                // }
                // --- HẾT PHẦN LOGIC ---

            });
        });
        // --- ĐẾN ĐÂY ---
    }

    /**
     * Create a personal team for the user.
     * (Hàm này dùng để tạo team cho Teacher)
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    protected function createTeam(User $user): void
    {
        $user->ownedTeams()->save(Team::forceCreate([
            'user_id' => $user->id,
            'name' => explode(' ', $user->name, 2)[0]."'s Team",
            'personal_team' => true,
        ]));
    }
}