<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Tự ghi lịch sử thêm / sửa / xóa của model vào bảng activity_log (lưu vĩnh viễn).
 * Gắn vào model: use \App\Traits\LogsActivity;
 * Tùy chọn trong model: protected static $logIgnore = ['field', ...];
 *
 * Lưu ý: chỉ bắt được thao tác qua Eloquent (create/update/save/delete trên model).
 * Câu lệnh DB::table(...)->update() hoặc Model::where(...)->delete() hàng loạt không phát sinh sự kiện nên không ghi.
 * Lỗi khi ghi log không bao giờ làm hỏng thao tác chính.
 */
trait LogsActivity
{
    public static function bootLogsActivity()
    {
        static::created(function ($m) {
            self::writeActivityLog($m, 'created', null, $m->getAttributes());
        });
        static::updated(function ($m) {
            $new = $m->getChanges();
            $old = [];
            foreach ($new as $k => $v) {
                $old[$k] = $m->getOriginal($k);
            }
            self::writeActivityLog($m, 'updated', $old, $new);
        });
        static::deleted(function ($m) {
            self::writeActivityLog($m, 'deleted', $m->getAttributes(), null);
        });
    }

    protected static function activityIgnoredFields()
    {
        $base = ['updated_at', 'created_at', 'updated_user', 'remember_token', 'password', 'api_token'];
        $extra = property_exists(static::class, 'logIgnore') ? static::$logIgnore : [];
        return array_merge($base, $extra);
    }

    protected static function writeActivityLog($m, $action, $old, $new)
    {
        try {
            $ignore = self::activityIgnoredFields();
            $clean = function ($arr) use ($ignore) {
                if ($arr === null) return null;
                foreach ($ignore as $f) unset($arr[$f]);
                foreach ($arr as $k => $v) {
                    if (is_string($v) && mb_strlen($v) > 2000) $arr[$k] = mb_substr($v, 0, 2000) . '…';
                }
                return $arr;
            };
            $old = $clean($old);
            $new = $clean($new);
            if ($action == 'updated' && empty($new)) return; // chỉ đổi updated_at...

            $req = app()->bound('request') ? request() : null;
            $isConsole = PHP_SAPI === 'cli';
            DB::table('activity_log')->insert([
                'user_id' => Auth::check() ? Auth::id() : null,
                'model' => $m->getTable(),
                'model_id' => $m->getKey(),
                'action' => $action,
                'old_values' => $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
                'new_values' => $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
                'request_uid' => app()->bound('request_uid') ? app('request_uid') : null,
                'url' => $isConsole ? 'console' : ($req ? mb_substr($req->method() . ' ' . $req->path(), 0, 500) : null),
                'ip' => ($req && !$isConsole) ? $req->ip() : null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            try { \Log::warning('activity_log: ' . $e->getMessage()); } catch (\Throwable $e2) {}
        }
    }
}
