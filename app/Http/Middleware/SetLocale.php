<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

/**
 * Đa ngôn ngữ (port từ Plan To Travel): chọn ngôn ngữ giao diện cho request.
 * Thứ tự: session('locale') -> users.locale của tài khoản -> 'vi' (mặc định).
 * Chuỗi giao diện viết bằng tiếng Việt và bọc __('...'); bản tiếng Anh ở resources/lang/en.json.
 */
class SetLocale
{
    const SUPPORTED = ['vi', 'en'];

    public function handle($request, Closure $next)
    {
        $locale = session('locale');

        if (!$locale && Auth::check()) {
            $locale = Auth::user()->locale ?? null;
            if ($locale) {
                session(['locale' => $locale]);
            }
        }

        if (!in_array($locale, self::SUPPORTED, true)) {
            $locale = 'vi';
        }

        App::setLocale($locale);

        return $next($request);
    }
}
