<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests; // <-- 1. Thêm dòng này
use Illuminate\Routing\Controller as BaseController; // <-- 2. Thêm dòng này

abstract class Controller extends BaseController // <-- 3. Sửa dòng này
{
    use AuthorizesRequests, ValidatesRequests; // <-- 4. Sửa dòng này
}