<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Perm;
use Auth, DB, Session;

/**
 * Quản lý nhóm quyền và gán quyền cho tài khoản. CHỈ tài khoản id 1.
 * Mọi thay đổi được ghi vào activity_log.
 */
class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::check() || !Perm::isSuperAdmin()) abort(403, __('Không có quyền'));
            return $next($request);
        });
    }

    protected function log($model, $id, $action, $old, $new)
    {
        try {
            DB::table('activity_log')->insert([
                'user_id' => Auth::id(), 'model' => $model, 'model_id' => $id, 'action' => $action,
                'old_values' => $old === null ? null : json_encode($old, JSON_UNESCAPED_UNICODE),
                'new_values' => $new === null ? null : json_encode($new, JSON_UNESCAPED_UNICODE),
                'request_uid' => app()->bound('request_uid') ? app('request_uid') : null,
                'url' => mb_substr(request()->method() . ' ' . request()->path(), 0, 500), 'ip' => request()->ip(),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {}
    }

    public function index()
    {
        $roles = DB::table('roles')->orderBy('status', 'desc')->orderBy('id')->get();
        $permCount = DB::table('role_permissions')->groupBy('role_id')->select('role_id', DB::raw('COUNT(*) c'))->pluck('c', 'role_id');
        $userCount = DB::table('users')->whereNotNull('role_id')->where('status', '>', 0)->groupBy('role_id')->select('role_id', DB::raw('COUNT(*) c'))->pluck('c', 'role_id');
        return view('phan-quyen.index', compact('roles', 'permCount', 'userCount'));
    }

    public function create(Request $request)
    {
        $role = (object) ['id' => null, 'name' => '', 'description' => '', 'all_branches' => 0, 'status' => 1];
        $perms = [];
        if ($request->copy) { // sao chép nhóm
            $src = DB::table('roles')->where('id', $request->copy)->first();
            if ($src) {
                $role->name = $src->name . ' (' . __('bản sao') . ')';
                $role->description = $src->description;
                $role->all_branches = $src->all_branches;
                $perms = DB::table('role_permissions')->where('role_id', $src->id)->pluck('permission')->all();
            }
        }
        return view('phan-quyen.form', ['role' => $role, 'perms' => $perms]);
    }

    public function edit($id)
    {
        $role = DB::table('roles')->where('id', $id)->first();
        abort_unless($role, 404);
        $perms = DB::table('role_permissions')->where('role_id', $id)->pluck('permission')->all();
        return view('phan-quyen.form', compact('role', 'perms'));
    }

    protected function validPerms($input)
    {
        $valid = [];
        foreach (config('permissions.modules') as $m => $cfg) {
            foreach ($cfg['actions'] as $a) $valid[] = "$m.$a";
        }
        return array_values(array_intersect((array) $input, $valid));
    }

    public function save(Request $request)
    {
        $this->validate($request, ['name' => 'required|max:100'], ['name.required' => __('Bạn chưa nhập tên nhóm')]);
        $data = [
            'name' => trim($request->name), 'description' => $request->description,
            'all_branches' => $request->all_branches ? 1 : 0, 'status' => $request->status === '0' ? 0 : 1,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $perms = $this->validPerms($request->perms);
        DB::transaction(function () use ($request, $data, $perms, &$id) {
            if ($request->id) {
                $id = (int) $request->id;
                $old = (array) DB::table('roles')->where('id', $id)->first();
                $oldPerms = DB::table('role_permissions')->where('role_id', $id)->pluck('permission')->all();
                DB::table('roles')->where('id', $id)->update($data);
                DB::table('role_permissions')->where('role_id', $id)->delete();
                $this->log('roles', $id, 'updated',
                    ['name' => $old['name'], 'all_branches' => $old['all_branches'], 'status' => $old['status'], 'permissions' => implode(', ', $oldPerms)],
                    ['name' => $data['name'], 'all_branches' => $data['all_branches'], 'status' => $data['status'], 'permissions' => implode(', ', $perms)]);
            } else {
                $data['created_at'] = $data['updated_at'];
                $id = DB::table('roles')->insertGetId($data);
                $this->log('roles', $id, 'created', null, ['name' => $data['name'], 'all_branches' => $data['all_branches'], 'permissions' => implode(', ', $perms)]);
            }
            foreach ($perms as $p) DB::table('role_permissions')->insert(['role_id' => $id, 'permission' => $p]);
        });
        Session::flash('message', __('Đã lưu nhóm quyền'));
        return redirect()->route('phan-quyen.index');
    }

    public function destroy($id)
    {
        $n = DB::table('users')->where('role_id', $id)->count();
        if ($n > 0) {
            Session::flash('message', __('Không xóa được: còn :n tài khoản thuộc nhóm này', ['n' => $n]));
            return redirect()->route('phan-quyen.index');
        }
        $old = DB::table('roles')->where('id', $id)->first();
        DB::table('role_permissions')->where('role_id', $id)->delete();
        DB::table('roles')->where('id', $id)->delete();
        if ($old) $this->log('roles', $id, 'deleted', ['name' => $old->name], null);
        Session::flash('message', __('Đã xóa nhóm quyền'));
        return redirect()->route('phan-quyen.index');
    }

    // ------------------------------------------------ gán quyền cho tài khoản
    public function users(Request $request)
    {
        $q = DB::table('users')->where('status', '>', 0);
        if ($request->keyword) {
            $k = '%' . $request->keyword . '%';
            $q->where(function ($w) use ($k) { $w->where('name', 'like', $k)->orWhere('email', 'like', $k)->orWhere('phone', 'like', $k); });
        }
        if ($request->role_id === 'none') $q->whereNull('role_id');
        elseif ($request->role_id) $q->where('role_id', (int) $request->role_id);
        $users = $q->orderBy('name')->paginate(50)->appends($request->except('page'));
        $roles = DB::table('roles')->where('status', 1)->orderBy('id')->get();
        $branches = DB::table('branch')->where('status', 1)->orderBy('display_order')->get();
        $ub = DB::table('user_branches')->whereIn('user_id', $users->pluck('id')->all() ?: [0])->get()
            ->groupBy('user_id')->map(function ($g) { return $g->pluck('branch_id')->map('intval')->all(); });
        $branchNames = DB::table('branch')->pluck('name', 'id');
        return view('phan-quyen.users', compact('users', 'roles', 'branches', 'ub', 'branchNames'));
    }

    public function saveUser(Request $request)
    {
        $uid = (int) $request->user_id;
        if (Perm::isSuperAdmin((object) ['id' => $uid])) {
            Session::flash('message', __('Không thay đổi quyền của tài khoản quản trị chính'));
            return back();
        }
        $user = DB::table('users')->where('id', $uid)->first();
        abort_unless($user, 404);
        $roleId = $request->role_id ? (int) $request->role_id : null;
        $branchIds = array_values(array_unique(array_map('intval', (array) $request->branch_ids)));
        $oldBranches = DB::table('user_branches')->where('user_id', $uid)->pluck('branch_id')->map('intval')->sort()->values()->all();
        sort($branchIds);
        DB::transaction(function () use ($uid, $user, $roleId, $branchIds, $oldBranches) {
            if ((int) $user->role_id !== (int) $roleId) {
                \App\Models\Account::where('id', $uid)->first()->update(['role_id' => $roleId]); // ghi lịch sử qua model
            }
            if ($oldBranches != $branchIds) {
                DB::table('user_branches')->where('user_id', $uid)->delete();
                foreach ($branchIds as $b) DB::table('user_branches')->insert(['user_id' => $uid, 'branch_id' => $b]);
                $this->log('user_branches', $uid, 'updated', ['branch_ids' => implode(', ', $oldBranches)], ['branch_ids' => implode(', ', $branchIds)]);
            }
        });
        Session::flash('message', __('Đã lưu quyền cho :name', ['name' => $user->name]));
        return back();
    }

    public function check($id)
    {
        $user = \App\User::findOrFail($id);
        Perm::flush();
        $active = Perm::active($user);
        $role = $user->role_id ? DB::table('roles')->where('id', $user->role_id)->first() : null;
        $perms = $role ? DB::table('role_permissions')->where('role_id', $role->id)->pluck('permission')->all() : [];
        $branchIds = $active ? Perm::branchIds($user) : null;
        $allBranches = DB::table('branch')->pluck('name', 'id');
        $menu = array_filter(config('permissions.menu'), function ($m) use ($user) { return Perm::can($m[1], $user); });
        Perm::flush();
        return view('phan-quyen.check', compact('user', 'active', 'role', 'perms', 'branchIds', 'allBranches', 'menu'));
    }
}
