<?php

namespace App\Http\Controllers\Backend\Auth\User;

use App\Models\Auth\Role;
use App\Models\Auth\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\Backend\Auth\User\UserDeleted;
use App\Repositories\Backend\Auth\RoleRepository;
use App\Repositories\Backend\Auth\UserRepository;
use App\Repositories\Backend\Auth\PermissionRepository;
use App\Http\Requests\Backend\Auth\User\StoreUserRequest;
use App\Http\Requests\Backend\Auth\User\ManageUserRequest;
use App\Http\Requests\Backend\Auth\User\UpdateUserRequest;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserController.
 */
class UserController extends Controller
{
    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * UserController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param ManageUserRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ManageUserRequest $request)
    {
        //dd("hh");
        if (!\Gate::allows('user_management_access')) {
            return abort(401);
        }
        $roles = Role::select('id','name')->get();

        //dd($this->userRepository->getActivePaginated(25, 'id', 'asc'));

        return view('backend.auth.user.index',compact('roles'))
            ->withUsers($this->userRepository->getActivePaginated(25, 'id', 'asc'));
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {

        
        if($request->role &&  $request->role != ""){
            $users = User::role($request->role)->with('roles', 'permissions', 'providers')
                ->orderBy('users.created_at', 'desc');
        }else{
            $users = User::with('roles', 'permissions', 'providers')
                ->whereNull('employee_type')
                ->orderBy('users.created_at', 'desc');
        }

        return \DataTables::of($users)
            ->addIndexColumn()
            ->addColumn('confirmed_label', function ($q)  {
                return $q->confirmed_label;
            })
            ->addColumn('roles_label', function ($q)  {
                return ($q->roles_label) ?? 'N/A';
            })
            ->addColumn('permissions_label', function ($q)  {
                return ($q->permission_label) ?? 'N/A';
            })
            ->addColumn('social_buttons', function ($q)  {
                return ($q->social_buttons) ?? 'N/A';
            })
            ->addColumn('updated_at', function ($q)  {
                \Log::info($q);

                return $q->updated_at->diffForHumans();
            })
            ->addColumn('last_updated', function ($q)  {
                return $q->updated_at->diffForHumans();
            })
            ->addColumn('actions', function ($q)  {
                return $q->action_buttons;
            })
            ->rawColumns(['confirmed_label','roles_label','permissions_label','social_buttons','actions'])
            ->make();
    }

    /**
     * @param ManageUserRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     *
     * @return mixed
     */
    public function create(ManageUserRequest $request, RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        return view('backend.auth.user.create')
            ->withRoles($roleRepository->with('permissions')->get(['id', 'name']))
            ->withPermissions($permissionRepository->get(['id', 'name']));
    }

    /**
     * @param StoreUserRequest $request
     *
     * @return mixed
     * @throws \Throwable
     */
    public function store(StoreUserRequest $request)
    {
        // echo '<pre>'; print_r($request->all());die;
        $this->userRepository->create($request->only(
            'first_name',
            'last_name',
            'email',
            'password',
            'active',
            'confirmed',
            'confirmation_email',
            'roles',
            'permissions',
            'employee_type'
        ));

        return redirect()->route('admin.auth.user.index')->withFlashSuccess(__('alerts.backend.users.created'));
    }

    /**
     * @param ManageUserRequest $request
     * @param User              $user
     *
     * @return mixed
     */
    public function show(ManageUserRequest $request, User $user)
    {
        return view('backend.auth.user.show')
            ->withUser($user);
    }

    /**
     * @param ManageUserRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     * @param User                 $user
     *
     * @return mixed
     */
    public function edit(ManageUserRequest $request, RoleRepository $roleRepository, PermissionRepository $permissionRepository, User $user)
    {
        return view('backend.auth.user.edit')
            ->withUser($user)
            ->withRoles($roleRepository->get())
            ->withUserRoles($user->roles->pluck('name')->all())
            ->withPermissions($permissionRepository->get(['id', 'name']))
            ->withUserPermissions($user->permissions->pluck('name')->all());
    }

    /**
     * @param UpdateUserRequest $request
     * @param User              $user
     *
     * @return mixed
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     */
    public function update(UpdateUserRequest $request, User $user)
    {

        $data = $request->only(
            'first_name',
            'last_name',
            'email',
            'roles',
            'permissions',
            'employee_type'
        );
       // Update password ONLY if user chose to change it
        if ($request->boolean('change_password') && $request->filled('password')) {
            $data['password'] = Hash::make( $request->password );
            //dd($data['password'], $request->password);
        }

        //dd();

        $this->userRepository->update($user, $data);
        // $this->userRepository->update($user, $request->only(
        //     'first_name',
        //     'last_name',
        //     'email',
        //     'roles',
        //     'permissions',
        //     'employee_type'
        // ));

        return redirect()->route('admin.auth.user.index')->withFlashSuccess(__('alerts.backend.users.updated'));
    }

    /**
     * @param ManageUserRequest $request
     * @param User              $user
     *
     * @return mixed
     * @throws \Exception
     */
    public function destroy(ManageUserRequest $request, User $user)
    {
        $this->userRepository->deleteById($user->id);

        event(new UserDeleted($user));

        return redirect()->route('admin.auth.user.index')->withFlashSuccess(__('alerts.backend.users.deleted'));
    }
}
