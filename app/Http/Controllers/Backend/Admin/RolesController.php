<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Auth\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRolesRequest;
use App\Http\Requests\Admin\UpdateRolesRequest;
use Spatie\Permission\Models\Permission;

class RolesController extends Controller
{
    /**
     * Display a listing of Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Gate::allows('role_access')) {
            return abort(401);
        }


        $roles = Role::all();
        //dd($roles);

        return view('backend.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating new Role.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (! Gate::allows('role_create')) {
            return abort(401);
        }

        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('_', $perm->name)[0]; // e.g., 'user', 'course', 'lesson'
        });

        return view('backend.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created Role in storage.
     *
     * @param  \App\Http\Requests\StoreRolesRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreRolesRequest $request)
    {
        if (! Gate::allows('role_create')) {
            return abort(401);
        }
        $role = Role::create([
                'name' => $request->name,
                'guard_name' => 'web',
            ]);
        //dd($request->all());
        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $role->syncPermissions($permissions);


        return redirect()->route('admin.roles.index');
    }


    /**
     * Show the form for editing Role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (! Gate::allows('role_edit')) {
            return abort(401);
        }
        $permissions = Permission::all()->groupBy(function ($perm) {
            return explode('_', $perm->name)[0];
        });

        $role = Role::findOrFail($id);
        //dd($role);
        if ($role->name == 'student') {
            $permissions = $permissions->reject(function ($group, $module) {
                return in_array($module, [
                    'trainer',
                    'trainee',
                    'calender',
                    'learning_pathway',
                    'reports',
                    'site_management',
                    'access_management',
                    'settings',
                    'send_email_notification',
                    'user',
                    'permission',
                    'role',
                    'course',
                    'lesson',
                    'question',
                    'backend',
                    'test',
                    'questions',
                    'feedback',
                    'assesment',
                    'manual_assesment'
                ]);
            });
        }

        return view('backend.roles.edit', compact('role', 'permissions'));
    }

    /**
     * Update Role in storage.
     *
     * @param  \App\Http\Requests\UpdateRolesRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateRolesRequest $request, $id)
    {
        //dd($request->all());
        if (! Gate::allows('role_edit')) {
            return abort(401);
        }
        $role = Role::findOrFail($id);
        $role->update($request->all());

        $permissionIds = $request->input('permissions', []);
        $permissions = Permission::whereIn('id', $permissionIds)->get();

        $role->syncPermissions($permissions);

        return redirect()->route('admin.roles.index');
    }


    /**
     * Display Role.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (! Gate::allows('role_view')) {
            return abort(401);
        }
        $permissions = Permission::get()->pluck('title', 'id');
        $users = \App\Models\Auth\User::whereHas(
            'role',
            function ($query) use ($id) {
                $query->where('id', $id);
            }
        )->get();

        $role = Role::findOrFail($id);

        return view('backend.roles.show', compact('role', 'users'));
    }


    /**
     * Remove Role from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (! Gate::allows('role_delete')) {
            return abort(401);
        }
        $role = Role::findOrFail($id);
        $role->delete();

        return redirect()->route('admin.roles.index');
    }

    /**
     * Delete all selected Role at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (! Gate::allows('role_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Role::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }
}
