<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Badge extends Model
{
    use HasFactory;

    // QUAN TRỌNG: Cho phép điền các trường này
    protected $fillable = [
        'name',
        'description',
        'icon_path',
    ];

    // Quan hệ với User (nếu cần truy ngược từ Badge ra những ai sở hữu nó)
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('awarded_at');
    }
}