<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Phân quyền (14/11/2026).
 * - Tài khoản id 1 (super admin) và tài khoản chưa gán nhóm (users.role_id NULL) => "legacy": giữ nguyên quyền cũ.
 * - Tài khoản đã gán nhóm => theo bảng role_permissions + giới hạn chi nhánh.
 */
class Perm
{
    protected static $cache = [];

    protected static function user()
    {
        return Auth::check() ? Auth::user() : null;
    }

    public static function isSuperAdmin($user = null)
    {
        $user = $user ?: self::user();
        return $user && in_array((int) $user->id, config('permissions.super_admin_ids', [1]));
    }

    /** Tài khoản đang dùng quyền mới (đã gán nhóm, không phải super admin). */
    public static function active($user = null)
    {
        $user = $user ?: self::user();
        if (!$user || self::isSuperAdmin($user)) return false;
        return !empty($user->role_id) && self::role($user->role_id) !== null;
    }

    public static function role($roleId)
    {
        $k = 'role_' . $roleId;
        if (!array_key_exists($k, self::$cache)) {
            try {
                self::$cache[$k] = DB::table('roles')->where('id', $roleId)->where('status', 1)->first();
            } catch (\Throwable $e) {
                self::$cache[$k] = null; // chưa chạy SQL
            }
        }
        return self::$cache[$k];
    }

    public static function permissions($roleId)
    {
        $k = 'perms_' . $roleId;
        if (!array_key_exists($k, self::$cache)) {
            try {
                self::$cache[$k] = DB::table('role_permissions')->where('role_id', $roleId)->pluck('permission')->all();
            } catch (\Throwable $e) {
                self::$cache[$k] = [];
            }
        }
        return self::$cache[$k];
    }

    /** Có quyền không. Tài khoản legacy luôn trả true (quyền cũ do code cũ tự kiểm tra). */
    public static function can($perm, $user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return false;
        if (!self::active($user)) return true;
        return in_array($perm, self::permissions($user->role_id));
    }

    /** Danh sách chi nhánh được phép; null = không giới hạn. */
    public static function branchIds($user = null)
    {
        $user = $user ?: self::user();
        if (!$user || !self::active($user)) return null;
        $role = self::role($user->role_id);
        if ($role && $role->all_branches) return null;
        $k = 'branches_' . $user->id;
        if (!array_key_exists($k, self::$cache)) {
            $ids = [];
            try {
                $ids = DB::table('user_branches')->where('user_id', $user->id)->pluck('branch_id')->map(function ($v) { return (int) $v; })->all();
            } catch (\Throwable $e) {}
            if (!$ids && !empty($user->branch_id)) $ids = [(int) $user->branch_id];
            self::$cache[$k] = $ids;
        }
        return self::$cache[$k];
    }

    public static function branchAllowed($branchId, $user = null)
    {
        $ids = self::branchIds($user);
        return $ids === null || in_array((int) $branchId, $ids, true);
    }

    /** Quyền cần cho 1 route; null = route không khai báo. */
    public static function routePermission($routeName)
    {
        $map = config('permissions.routes', []);
        if (isset($map[$routeName])) return $map[$routeName];
        foreach ($map as $pattern => $perm) {
            if (substr($pattern, -1) === '*' && strpos($routeName, substr($pattern, 0, -1)) === 0) return $perm;
        }
        return null;
    }

    /** Route đầu tiên trong menu mà tài khoản được xem (dùng khi chuyển hướng). */
    public static function homeRoute($user = null)
    {
        foreach (config('permissions.menu', []) as $m) {
            if (self::can($m[1], $user)) return $m[0];
        }
        return 'account.change-pass';
    }

    public static function flush()
    {
        self::$cache = [];
    }
}
