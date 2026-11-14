<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Ghi nhật ký thao tác (request_log): mọi request gửi dữ liệu (POST/PUT/PATCH/DELETE)
 * và các link GET làm thay đổi dữ liệu (destroy, delete, change-value, cancel, recalc...).
 * KHÔNG ghi lượt xem trang. Bỏ mật khẩu / token. Tự xóa bản ghi cũ hơn 12 tháng (mỗi ngày 1 lần).
 */
class LogUserRequests
{
    const KEEP_MONTHS = 12;
    const SKIP_ROUTES = ['filter-prefs.save'];
    const GET_ACTION_PATTERN = '/(destroy|delete|xoa|change-value|change-status|cancel|recalc|done|approve|duyet)/i';
    const SECRET_KEYS = ['_token', 'password', 'password_confirmation', 'current_password', 'old_password', 'new_password', 'api_token'];

    public function handle($request, Closure $next)
    {
        $uid = substr(str_replace('-', '', md5(uniqid('', true) . mt_rand())), 0, 24);
        app()->instance('request_uid', $uid);
        $start = microtime(true);

        $response = $next($request);

        try {
            $route = optional($request->route())->getName();
            $method = strtoupper($request->method());
            $isWrite = !in_array($method, ['GET', 'HEAD', 'OPTIONS']);
            $isGetAction = $method == 'GET' && preg_match(self::GET_ACTION_PATTERN, ($route ?: '') . ' ' . $request->path());
            if (($isWrite || $isGetAction) && !in_array($route, self::SKIP_ROUTES)) {
                $params = $request->except(self::SECRET_KEYS);
                $params = $this->sanitize($params);
                foreach ($request->allFiles() as $k => $f) {
                    $params['_files'][$k] = $this->fileNames($f);
                }
                $json = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
                if (mb_strlen($json) > 20000) $json = mb_substr($json, 0, 20000) . '…';
                DB::table('request_log')->insert([
                    'request_uid' => $uid,
                    'user_id' => Auth::check() ? Auth::id() : null,
                    'method' => $method,
                    'route' => $route ? mb_substr($route, 0, 150) : null,
                    'url' => mb_substr($request->getRequestUri(), 0, 500),
                    'params' => $json,
                    'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                    'ip' => $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
                    'duration_ms' => (int) round((microtime(true) - $start) * 1000),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $this->cleanupDaily();
        } catch (\Throwable $e) {
            try { \Log::warning('request_log: ' . $e->getMessage()); } catch (\Throwable $e2) {}
        }
        return $response;
    }

    private function sanitize($arr)
    {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $arr[$k] = $this->sanitize($v);
            } elseif (is_string($v)) {
                if (strlen($v) > 2000) $arr[$k] = substr($v, 0, 200) . '…[' . strlen($v) . ' bytes]'; // vd ảnh base64
            } elseif (is_object($v)) {
                $arr[$k] = '[object]';
            }
            if (is_string($k) && preg_match('/pass|token|secret/i', $k)) $arr[$k] = '***';
        }
        return $arr;
    }

    private function fileNames($f)
    {
        if (is_array($f)) return array_map([$this, 'fileNames'], $f);
        return method_exists($f, 'getClientOriginalName') ? $f->getClientOriginalName() : '[file]';
    }

    /** Xóa request_log cũ hơn 12 tháng, tối đa 1 lần/ngày. activity_log giữ vĩnh viễn. */
    private function cleanupDaily()
    {
        $key = 'request_log_cleanup_' . date('Ymd');
        if (!Cache::add($key, 1, 60 * 60 * 26)) return;
        DB::table('request_log')
            ->where('created_at', '<', date('Y-m-d H:i:s', strtotime('-' . self::KEEP_MONTHS . ' months')))
            ->delete();
    }
}
