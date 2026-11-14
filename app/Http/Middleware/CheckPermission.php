<?php

namespace App\Http\Middleware;

use Closure;
use App\Services\Perm;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Kiểm tra phân quyền cho tài khoản đã gán nhóm quyền (users.role_id).
 *  1. Route phải có trong config/permissions.php (routes / always), không có => chặn.
 *  2. Giới hạn chi nhánh: ép bộ lọc branch_id / branch_ids về chi nhánh được phép,
 *     chặn thêm / sửa / xóa bản ghi thuộc chi nhánh khác.
 *  3. Bill ngày cũ: sửa / xóa cần quyền bill.edit_old.
 * Tài khoản id 1 và tài khoản chưa gán nhóm: bỏ qua toàn bộ (giữ nguyên quyền cũ).
 */
class CheckPermission
{
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Perm::active()) {
            return $next($request);
        }
        $route = $request->route();
        $name = $route ? $route->getName() : null;
        $middlewares = $route ? $route->gatherMiddleware() : [];
        $isAuthRoute = in_array('auth', $middlewares);

        if ($isAuthRoute && !in_array($name, config('permissions.always', []))) {
            $perm = $name ? Perm::routePermission($name) : null;
            if ($perm === null || !Perm::can($perm)) {
                // trang chủ mà không có quyền xem bill -> chuyển tới trang đầu tiên được phép
                if ($name === 'dashboard' && !$request->ajax()) {
                    return redirect()->route(Perm::homeRoute());
                }
                return $this->deny($request, __('Bạn không có quyền thực hiện thao tác này.'));
            }
        }

        // ---- giới hạn chi nhánh
        $allowed = Perm::branchIds();
        if ($allowed !== null) {
            if (!$allowed) {
                return $this->deny($request, __('Tài khoản chưa được gán chi nhánh.'));
            }
            // bộ lọc danh sách / báo cáo
            if ($request->has('branch_ids') || in_array($name, ['booking.index', 'cost.index', 'cost.export', 'booking.excel', 'cost.nhap-hang', 'cost.bieu-do-gia', 'cost.canh-bao-nhap'])) {
                $ids = array_values(array_intersect(array_map('intval', (array) $request->input('branch_ids', [])), $allowed));
                $request->merge(['branch_ids' => $ids ?: $allowed]);
            }
            $isWrite = !in_array(strtoupper($request->method()), ['GET', 'HEAD']);
            if ($request->has('branch_id') || in_array($name, ['report.dish', 'report.chi-phi-doanh-thu', 'report.doanh-thu-thang', 'report.doanh-thu-thang-new'])) {
                $b = (int) $request->input('branch_id');
                if (!in_array($b, $allowed, true)) {
                    if ($isWrite && $b) {
                        return $this->deny($request, __('Bạn không có quyền với chi nhánh này.'));
                    }
                    $request->merge(['branch_id' => $allowed[0]]);
                }
            }
            // nhập nhiều chi phí qua API
            if ($name === 'cost.api.store-batch') {
                foreach ((array) $request->input('costs', []) as $c) {
                    if (!in_array((int) ($c['branch_id'] ?? 0), $allowed, true)) {
                        return $this->deny($request, __('Bạn không có quyền với chi nhánh này.'));
                    }
                }
            }
        }

        // ---- kiểm tra theo bản ghi
        $checks = config('permissions.record_checks', []);
        if ($name && isset($checks[$name])) {
            $cfg = $checks[$name];
            $id = $route->parameter($cfg['param']) ?? $request->input($cfg['param']);
            if ($id) {
                $row = DB::table($cfg['table'])->where('id', (int) $id)->first();
                if ($row) {
                    if (isset($row->branch_id) && !Perm::branchAllowed($row->branch_id)) {
                        return $this->deny($request, __('Bạn không có quyền với chi nhánh này.'));
                    }
                    if (!empty($cfg['old_perm']) && !Perm::can($cfg['old_perm'])) {
                        $date = $row->use_date ?? null;
                        if ($date && $date < date('Y-m-d')) {
                            return $this->deny($request, __('Chỉ được sửa / xóa bill trong ngày.'));
                        }
                    }
                }
            }
        }
        return $next($request);
    }

    protected function deny($request, $msg)
    {
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['ok' => false, 'message' => $msg, 'errors' => [$msg]], 403);
        }
        abort(403, $msg);
    }
}
