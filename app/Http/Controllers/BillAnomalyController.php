<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BillAnomalyService;
use App\Services\Perm;
use Auth, DB;

/** Cảnh báo bill bất thường. Tài khoản quyền cũ: chỉ role 1; tài khoản có nhóm quyền: theo quyền bill_alert.* */
class BillAnomalyController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || (!Perm::active() && Auth::user()->role != 1)) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $ymd = function ($v, $def) { return preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? $v : $def; };
        $f = [
            'from' => $ymd($request->from, date('Y-m-d', strtotime('-6 days'))),
            'to' => $ymd($request->to, date('Y-m-d')),
            'type' => array_key_exists($request->type, BillAnomalyService::$typeNames) ? $request->type : null,
            'branch_id' => $request->branch_id ? (int) $request->branch_id : null,
            'user_id' => $request->user_id ? (int) $request->user_id : null,
            'reviewed' => $request->has('reviewed') ? $request->reviewed : '0',
        ];
        $items = BillAnomalyService::find($f['from'], $f['to'], Perm::branchIds(), $f);
        $counts = [];
        foreach (BillAnomalyService::find($f['from'], $f['to'], Perm::branchIds(), ['branch_id' => $f['branch_id'], 'reviewed' => $f['reviewed']]) as $i) {
            $counts[$i->type] = ($counts[$i->type] ?? 0) + 1;
        }
        $userIds = [];
        foreach ($items as $i) { if ($i->user_id) $userIds[] = $i->user_id; if ($i->reviewed_by) $userIds[] = $i->reviewed_by; }
        $users = DB::table('users')->whereIn('id', array_unique($userIds) ?: [0])->pluck('name', 'id');
        $allUsers = DB::table('users')->where('status', '>', 0)->orderBy('name')->pluck('name', 'id');
        $branches = \App\Models\Branch::where('status', 1)->orderBy('display_order')->pluck('name', 'id');
        $collecters = DB::table('collecter')->pluck('name', 'id');
        $dishIds = [];
        foreach ($items as $i) if (!empty($i->detail['dish_id'])) $dishIds[] = $i->detail['dish_id'];
        $dishes = DB::table('dish')->whereIn('id', $dishIds ?: [0])->pluck('name', 'id');
        $typeNames = array_map('__', BillAnomalyService::$typeNames);
        return view('bill-anomaly.index', compact('items', 'counts', 'f', 'users', 'allUsers', 'branches', 'collecters', 'dishes', 'typeNames'));
    }

    /** Đánh dấu đã xem / bỏ đánh dấu (POST keys[], undo) */
    public function review(Request $request)
    {
        $keys = array_slice(array_filter((array) $request->input('keys', []), function ($k) {
            return is_string($k) && preg_match('/^[a-z]+:[0-9:\-]+$/', $k);
        }), 0, 500);
        if ($request->undo) {
            DB::table('bill_anomaly_reviews')->whereIn('anomaly_key', $keys ?: ['-'])->delete();
        } else {
            foreach ($keys as $k) {
                $parts = explode(':', $k);
                DB::table('bill_anomaly_reviews')->updateOrInsert(['anomaly_key' => $k], [
                    'booking_id' => in_array($parts[0], ['discount', 'cancel', 'old', 'many']) ? ((int) ($parts[1] ?? 0) ?: null) : null, 'reviewed_by' => Auth::id(), 'reviewed_at' => date('Y-m-d H:i:s'),
                    'note' => $request->note ? mb_substr($request->note, 0, 255) : null,
                ]);
            }
        }
        if ($request->ajax() || $request->wantsJson()) return new \Illuminate\Http\JsonResponse(['ok' => true, 'n' => count($keys)]);
        return back();
    }
}
