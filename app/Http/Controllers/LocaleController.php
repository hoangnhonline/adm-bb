<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/** Đổi ngôn ngữ giao diện (VI / EN): lưu vào session và vào users.locale của tài khoản. */
class LocaleController extends Controller
{
    public function change(Request $request, $lang)
    {
        if (!in_array($lang, SetLocale::SUPPORTED, true)) {
            abort(404);
        }
        session(['locale' => $lang]);
        if (Auth::check()) {
            try {
                DB::table('users')->where('id', Auth::id())->update(['locale' => $lang]);
            } catch (\Exception $e) {
                // chưa chạy SQL thêm cột users.locale -> vẫn đổi được theo phiên đăng nhập
                Log::warning('Locale: không lưu được users.locale - ' . $e->getMessage());
            }
        }
        return redirect()->back();
    }
}
