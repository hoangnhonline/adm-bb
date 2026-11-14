<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth, DB;

/**
 * Xem lịch sử thao tác. CHỈ tài khoản id 1 (anh Hoàng) được xem.
 *  - tab=data   : thay đổi dữ liệu (activity_log, lưu vĩnh viễn)
 *  - tab=action : nhật ký thao tác (request_log, giữ 12 tháng)
 */
class ActivityLogController extends Controller
{
    const VIEWER_IDS = [1];

    public static $modelNames = [
        'booking' => 'Bill',
        'booking_detail' => 'Món trong bill',
        'booking_payment' => 'Thanh toán bill',
        'cost' => 'Chi phí',
        'cost_detail' => 'Chi tiết chi phí',
        'cost_payment' => 'Thanh toán chi phí',
        'cost_type' => 'Loại chi phí',
        'partners' => 'Đối tác / NCC cũ',
        'partner_transactions' => 'Giao dịch công nợ đối tác',
        'dish' => 'Món ăn',
        'users' => 'Tài khoản',
        'nha_cung_cap' => 'Nhà cung cấp',
        'ingredient' => 'Nguyên liệu',
        'branch' => 'Chi nhánh',
        'collecter' => 'Người thu / chi',
    ];
    public static $actionNames = ['created' => 'Thêm', 'updated' => 'Sửa', 'deleted' => 'Xóa'];

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !in_array(Auth::user()->id, self::VIEWER_IDS)) abort(403, 'Không có quyền');
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tab = $request->tab == 'action' ? 'action' : 'data';
        $f = [
            'user_id' => $request->user_id,
            'model' => $request->model,
            'model_id' => $request->model_id,
            'action' => $request->action,
            'from' => $request->from ?: date('Y-m-d', strtotime('-7 days')),
            'to' => $request->to ?: date('Y-m-d'),
            'keyword' => trim((string) $request->keyword),
            'request_uid' => $request->request_uid,
        ];
        // xem lịch sử 1 đối tượng cụ thể: bỏ giới hạn ngày
        if ($f['model'] && $f['model_id'] && !$request->from) $f['from'] = '2000-01-01';

        if ($tab == 'data') {
            $q = DB::table('activity_log');
            if ($f['model']) $q->where('model', $f['model']);
            if ($f['model_id']) $q->where('model_id', (int) $f['model_id']);
            if ($f['action']) $q->where('action', $f['action']);
            if ($f['keyword']) $q->where(function ($w) use ($f) {
                $w->where('old_values', 'like', '%' . $f['keyword'] . '%')->orWhere('new_values', 'like', '%' . $f['keyword'] . '%');
            });
        } else {
            $q = DB::table('request_log');
            if ($f['keyword']) $q->where(function ($w) use ($f) {
                $w->where('url', 'like', '%' . $f['keyword'] . '%')->orWhere('route', 'like', '%' . $f['keyword'] . '%')
                  ->orWhere('params', 'like', '%' . $f['keyword'] . '%');
            });
        }
        if ($f['request_uid']) {
            $q->where('request_uid', $f['request_uid']);
        } else {
            $q->where('created_at', '>=', $f['from'] . ' 00:00:00')->where('created_at', '<=', $f['to'] . ' 23:59:59');
        }
        if ($f['user_id']) $q->where('user_id', (int) $f['user_id']);

        $items = $q->orderBy('id', 'desc')->paginate(50)->appends($request->except('page'));

        $userIds = DB::table('activity_log')->whereNotNull('user_id')->distinct()->pluck('user_id')
            ->merge(DB::table('request_log')->whereNotNull('user_id')->distinct()->pluck('user_id'))->unique();
        $userArr = DB::table('users')->whereIn('id', $userIds->all() ?: [0])->orderBy('name')->pluck('name', 'id');
        $modelNames = self::$modelNames;
        $actionNames = self::$actionNames;
        return view('activity-log.index', compact('tab', 'f', 'items', 'userArr', 'modelNames', 'actionNames'));
    }

    /** Link xem đối tượng gốc */
    public static function objectUrl($model, $id)
    {
        try {
            if ($model == 'booking') return route('booking.edit', $id);
            if ($model == 'cost') return route('cost.edit', $id);
            if ($model == 'partners') return route('doi-tac.show', $id);
        } catch (\Throwable $e) {}
        return null;
    }
}
