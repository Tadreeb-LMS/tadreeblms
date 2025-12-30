<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreTeachersRequest;
use App\Http\Requests\Admin\UpdateTeachersRequest;
use App\Models\Auth\User;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use DB;

class TeachersController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.teachers.index');
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $teachers = "";


        if (request('show_deleted') == 1) {
            $teachers = User::query()->role('teacher')->onlyTrashed()->orderBy('created_at', 'desc');
        } else {
            $teachers = User::query()->role('teacher')->orderBy('created_at', 'desc');
        }

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }

        //    $query = str_replace(array('?'), array('\'%s\''), $teachers->toSql());
        //    $query = vsprintf($query, $teachers->getBindings());
        //    dump($query);
        //    die;

        return DataTables::of($teachers)
            ->addIndexColumn()
            // ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
            //     $view = "";
            //     $edit = "";
            //     $delete = "";
            //     if ($request->show_deleted == 1) {
            //         return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.teachers', 'label' => 'id', 'value' => $q->id]);
            //     }

            //     if ($has_view) {
            //         $view = view('backend.datatable.action-view')
            //             ->with(['route' => route('admin.teachers.show', ['teacher' => $q->id])])->render();
            //     }

            //     if ($has_edit) {
            //         $edit = view('backend.datatable.action-edit')
            //             ->with(['route' => route('admin.teachers.edit', ['teacher' => $q->id])])
            //             ->render();
            //         $view .= $edit;
            //     }

            //     if ($has_delete) {
            //         $delete = view('backend.datatable.action-delete')
            //             ->with(['route' => route('admin.teachers.destroy', ['teacher' => $q->id])])
            //             ->render();
            //         $view .= $delete;
            //     }

            //     $view .= '<a class="btn btn-warning text-white mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

            //     return $view;
            // })
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
    if ($request->show_deleted == 1) {
        return view('backend.datatable.action-trashed')->with([
            'route_label' => 'admin.teachers',
            'label' => 'id',
            'value' => $q->id
        ]);
    }

    $view = '';
    $edit = '';
    $delete = '';

    if ($has_view) {
        $view = view('backend.datatable.action-view')
            ->with(['route' => route('admin.teachers.show', ['teacher' => $q->id])])
            ->render();
    }

    if ($has_edit) {
        $edit = view('backend.datatable.action-edit')
            ->with(['route' => route('admin.teachers.edit', ['teacher' => $q->id])])
            ->render();
    }

    if ($has_delete) {
        $delete = view('backend.datatable.action-delete')
            ->with(['route' => route('admin.teachers.destroy', ['teacher' => $q->id])])
            ->render();
    }

    $courseLink = '<a title="Courses" class="" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">
         <i class="fa fa-address-book" aria-hidden="true"></i>  </a>';

    // Wrap all actions in a flexbox container with spacing
    return '<div class="action-pill" >' . $view . $edit . $delete . $courseLink . '</div>';
})
//             ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
//     if ($request->show_deleted == 1) {
//         return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.teachers', 'label' => 'id', 'value' => $q->id]);
//     }

//     $dropdown = '<div class="dropdown">
//         <a class="dropdown-toggle" type="button" id="dropdownMenuButton'.$q->id.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
//             <i class="fa fa-ellipsis-v action-icon" aria-hidden="true"></i>
//         </a>
//         <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton'.$q->id.'">';

//     if ($has_view) {
//         $dropdown .= view('backend.datatable.action-view')
//             ->with(['route' => route('admin.teachers.show', ['teacher' => $q->id])])
//             ->render();
//     }

//     if ($has_edit) {
//         $dropdown .= view('backend.datatable.action-edit')
//             ->with(['route' => route('admin.teachers.edit', ['teacher' => $q->id])])
//             ->render();
//     }

//     if ($has_delete) {
//         $dropdown .= view('backend.datatable.action-delete')
//             ->with(['route' => route('admin.teachers.destroy', ['teacher' => $q->id])])
//             ->render();
//     }

//     $dropdown .= '<a class="dropdown-item pt-2 pl-3" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';
    
//     $dropdown .= '</div></div>';

//     return $dropdown;
// })

            // ->addColumn('status', function ($q) {
            //     $html = html()->label(html()->checkbox('')->id($q->id)
            //         ->checked(($q->active == 1) ? true : false)->class('switch-input')->attribute('data-id', $q->id)->value(($q->active == 1) ? 1 : 0) . '<span class="switch-label"></span><span class="switch-handle"></span>')->class('switch switch-lg switch-3d switch-primary');
            //     return $html;
            //     // return ($q->active == 1) ? "Enabled" : "Disabled";
            // })
            ->addColumn('status', function ($q) {
    $checked = $q->active == 1 ? 'checked' : '';
    $html = '<div class="custom-control custom-switch">
                <input type="checkbox" 
                       class="custom-control-input status-toggle switch-input" 
                       id="switch' . $q->id . '" 
                       data-id="' . $q->id . '" 
                       ' . $checked . '>
                <label class="custom-control-label" for="switch' . $q->id . '"></label>
            </div>';
    return $html;
})
            ->rawColumns(['actions', 'image', 'status'])
            ->make();
    }

    /**
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = DB::table('master_countries')->get();
        return view('backend.teachers.create' , compact('countries'));
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreTeachersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTeachersRequest $request)
    {
        //    $request = $this->saveFiles($request);
        $teacher = User::create($request->all());
        // dd($teacher);
        $teacher->confirmed = 1;
        if ($request->image) {
            $teacher->avatar_type = 'storage';
            $teacher->avatar_location = $request->image->store('/avatars', 'public');
        }

        if ($request->image) {
            $teacher->cv_file = $request->cv_file->store('/cv_file', 'public');
        }

        $teacher->id_number = $request->id_number;
        $teacher->classfi_number = $request->classfi_number;
        $teacher->nationality = $request->nationality;
        $teacher->active = isset($request->active) ? 1 : 0;
        $teacher->save();
        $teacher->assignRole('teacher');

        /*

        $payment_details = [
            'bank_name'         => request()->payment_method == 'bank'?request()->bank_name:'',
            'ifsc_code'         => request()->payment_method == 'bank'?request()->ifsc_code:'',
            'account_number'    => request()->payment_method == 'bank'?request()->account_number:'',
            'account_name'      => request()->payment_method == 'bank'?request()->account_name:'',
            'paypal_email'      => request()->payment_method == 'paypal'?request()->paypal_email:'',
        ];
        */
        $data = [
            'user_id'           => $teacher->id,
            'facebook_link'     => request()->facebook_link,
            'twitter_link'      => request()->twitter_link,
            'linkedin_link'     => request()->linkedin_link,
            //'payment_method'    => request()->payment_method,
            //'payment_details'   => json_encode($payment_details),
            'description'       => request()->description,
        ];
        TeacherProfile::create($data);

        return redirect()->route('admin.teachers.show', ['teacher' => $teacher->id])->withFlashSuccess(trans('alerts.backend.general.created'));
        // return redirect()->route('admin.courses.create')->withFlashSuccess(__('Please add course here...'));
        // return redirect()->route('admin.teachers.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $teacher = User::findOrFail($id);
        $countries = DB::table('master_countries')->get();
        return view('backend.teachers.edit', compact('teacher','countries'));
    }

    /**
     * Update Category in storage.
     *
     * @param  \App\Http\Requests\UpdateTeachersRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTeachersRequest $request, $id)
    {
        //        $request = $this->saveFiles($request);

        $teacher = User::findOrFail($id);
        $teacher->update($request->except('email'));
        if ($request->has('image')) {
            $teacher->avatar_type = 'storage';
            $teacher->avatar_location = $request->image->store('/avatars', 'public');
        }
        if ($request->cv_file) {
            $teacher->cv_file = $request->cv_file->store('/cv_file', 'public');
        }

        $teacher->id_number = $request->id_number;
        $teacher->classfi_number = $request->classfi_number;
        $teacher->nationality = $request->nationality;
        $teacher->active = isset($request->active) ? 1 : 0;
        $teacher->save();

        /*
        $payment_details = [
            'bank_name'         => request()->payment_method == 'bank'?request()->bank_name:'',
            'ifsc_code'         => request()->payment_method == 'bank'?request()->ifsc_code:'',
            'account_number'    => request()->payment_method == 'bank'?request()->account_number:'',
            'account_name'      => request()->payment_method == 'bank'?request()->account_name:'',
            'paypal_email'      => request()->payment_method == 'paypal'?request()->paypal_email:'',
        ];
        */
        $data = [
            // 'user_id'           => $user->id,
            'facebook_link'     => request()->facebook_link,
            'twitter_link'      => request()->twitter_link,
            'linkedin_link'     => request()->linkedin_link,
            //'payment_method'    => request()->payment_method,
            //'payment_details'   => json_encode($payment_details),
            'description'       => request()->description,
        ];
        $teacher->teacherProfile->update($data);


        return redirect()->route('admin.teachers.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacher = User::findOrFail($id);

        return view('backend.teachers.show', compact('teacher'));
    }


    /**
     * Remove Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $teacher = User::findOrFail($id);

        if ($teacher->courses->count() > 0) {
            return redirect()->route('admin.teachers.index')->withFlashDanger(trans('alerts.backend.general.teacher_delete_warning'));
        } else {
            $teacher->delete();
        }

        return redirect()->route('admin.teachers.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Category at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if ($request->input('ids')) {
            $entries = User::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $teacher = User::onlyTrashed()->findOrFail($id);
        $teacher->restore();

        return redirect()->route('admin.teachers.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        $teacher = User::onlyTrashed()->findOrFail($id);
        $teacher->teacherProfile->delete();
        $teacher->forceDelete();

        return redirect()->route('admin.teachers.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }


    /**
     * Update teacher status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     **/
    public function updateStatus()
    {
        $teacher = User::find(request('id'));
        $teacher->active = $teacher->active == 1 ? 0 : 1;
        $teacher->save();
    }
}
