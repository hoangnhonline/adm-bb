<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * [BỘ LỌC TÙY CHỌN] Lưu / đọc danh sách ô filter mà user chọn hiển thị trên từng trang (bill, chi phí).
 * Port từ Plan To Travel (13/11/2026). Bảng: user_filter_prefs (database/sql/2026_11_14_user_filter_prefs.sql)
 */
class FilterPrefController extends Controller
{
    /** Đọc cấu hình của user hiện tại cho 1 trang; null = chưa lưu (dùng mặc định). */
    public static function getForPage($page)
    {
        if (!Auth::check() || !$page) {
            return null;
        }
        try {
            $row = DB::table('user_filter_prefs')->where('user_id', Auth::id())->where('page', $page)->first();
        } catch (\Exception $e) {
            return null; // chưa chạy file SQL tạo bảng
        }
        if (!$row || $row->visible_keys === null) {
            return null;
        }
        $keys = json_decode($row->visible_keys, true);
        return is_array($keys) ? array_values($keys) : null;
    }

    public function save(Request $request)
    {
        $page = (string) $request->input('page', '');
        if ($page === '' || strlen($page) > 100 || !preg_match('/^[A-Za-z0-9._\-]+$/', $page)) {
            return response()->json(['ok' => false, 'message' => 'Invalid page'], 422);
        }

        $reset = (int) $request->input('reset', 0) === 1;
        $keys = $request->input('keys', []);
        if (!is_array($keys)) {
            $keys = [];
        }
        $keys = array_values(array_unique(array_filter(array_map(function ($k) {
            return is_string($k) ? mb_substr($k, 0, 150) : null;
        }, array_slice($keys, 0, 200)))));

        try {
            if ($reset) {
                DB::table('user_filter_prefs')->where('user_id', Auth::id())->where('page', $page)->delete();
            } else {
                $now = date('Y-m-d H:i:s');
                $exists = DB::table('user_filter_prefs')->where('user_id', Auth::id())->where('page', $page)->exists();
                if ($exists) {
                    DB::table('user_filter_prefs')->where('user_id', Auth::id())->where('page', $page)
                        ->update(['visible_keys' => json_encode($keys, JSON_UNESCAPED_UNICODE), 'updated_at' => $now]);
                } else {
                    DB::table('user_filter_prefs')->insert([
                        'user_id' => Auth::id(), 'page' => $page,
                        'visible_keys' => json_encode($keys, JSON_UNESCAPED_UNICODE),
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::warning('FilterPref: không lưu được - ' . $e->getMessage());
            return response()->json(['ok' => false, 'message' => __('Không lưu được cấu hình bộ lọc')], 500);
        }

        return response()->json(['ok' => true]);
    }
}
