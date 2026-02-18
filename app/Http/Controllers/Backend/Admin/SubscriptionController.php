<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdatePagesRequest;
use App\Models\Page;
use App\Models\Department;
use App\Models\Stripe\SubscribeCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use DB;
use Illuminate\Support\Str;


class SubscriptionController extends Controller
{
    use FileUploadTrait;
    private $tags;

    public function index()
    {
        if (!Gate::allows('page_access')) {
            return abort(401);
        }
        // Grab all the pages
        //$pages = SubscribeCourse::all();
        //dd($pages);
        // Show the page
        return view('backend.subscription.index', []);

    }


    


    /**
     * Display a listing of Lessons via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $pages = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('page_delete')) {
                return abort(401);
            }
            $pages = SubscribeCourse::onlyTrashed()->orderBy('created_at', 'desc');

        } else {
            $pages = SubscribeCourse::orderBy('created_at', 'desc');

        }

        //dd($pages);


        if (auth()->user()->can('page_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('page_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('page_delete')) {
            $has_delete = true;
        }

        $result = DataTables::of($pages)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.subscription', 'label' => 'id', 'value' => $q->id]);
                }
                /*
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.subscription.show', ['page' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.subscription.edit', ['page' => $q->id])])
                        ->render();
                    $view .= $edit;
                }
                */
                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.subscription.destroy', ['page' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                return $view;

            })

            ->editColumn('image', function ($q) {
                return ($q->image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->image) . '">' : 'N/A';
            })
            ->editColumn('course_id', function ($q) {
                return $q->course ? $q->course->title : '';
            })
            ->editColumn('user_name', function ($q) {
                return $q->user ? $q->user->first_name : '';
            })
            ->editColumn('email', function ($q) {
                return $q->user ? $q->user->email : '';
            })
            ->addColumn('status', function ($q) {
                $html = html()->label(html()->checkbox('')->id($q->id)
                ->checked(($q->status == 1) ? true : false)->class('switch-input')->attribute('data-id', $q->id)->value(($q->status == 1) ? 1 : 0).'<span class="switch-label"></span><span class="switch-handle"></span>')->class('switch switch-lg switch-3d switch-primary');
                return $html;
            })
            ->addColumn('position', function ($q) {
                return $q->user ? $q->user->getPosition() : '';
            })
            ->addColumn('created', function ($q) {
                return date('d/m/Y',strtotime($q->created_at));
            })
            ->rawColumns(['image','course_id','user_name','email', 'actions','status'])
            ->make();
        return $result;
        echo "<pre>";
        print_r($result);
        exit();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        if (!Gate::allows('page_create')) {
            return abort(401);
        }
        return view('backend.subscription.create');

    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StoreDepartmentRequest $request)
    {
        //dd($request->all());
        $page = new Department();
        $page->title = $request->title;
        if($request->slug == ""){
            $page->slug = Str::slug($request->title);
        }else{
            $page->slug = $request->slug;
        }
        $message = $request->get('content');
        $dom = new \DOMDocument();
        $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        
        $page->content = $dom->saveHTML();

       
        $page->user_id = auth()->user()->id;
        $page->published = 1;
        $page->sidebar = 1;
        $page->save();



        if ($page->id) {
            return redirect()->route('admin.subscription.index')->withFlashSuccess(__('alerts.backend.general.created'));
        } else {
            return redirect()->route('admin.subscription.index')->withFlashDanger(__('alerts.backend.general.error'));

        }
    }

    /**
     * Display the specified resource.
     *
     * @param  Page $page
     * @return view
     */
    public function show($id)
    {
        if (!Gate::allows('page_view')) {
            return abort(401);
        }
        $page = Department::findOrFail($id);
        return view('backend.subscription.show', compact('page'));

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  Page $page
     * @return view
     */
    public function edit($id)
    {
        if (!Gate::allows('page_edit')) {
            return abort(401);
        }
        $page = Department::where('id', '=', $id)->first();
        return view('backend.subscription.edit', compact('page'));

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function update(UpdatePagesRequest $request,$id)
    {
        ini_set('memory_limit', '-1');
        
        $page = Department::findOrFail($id);
        $page->title = $request->title;
        if($request->slug == ""){
            $page->slug = Str::slug($request->title);
        }else{
            $page->slug = $request->slug;
        }

        $message = $request->get('content');
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'));
       
        $page->content = $dom->saveHTML();

       
        $page->meta_title = $request->meta_title;
        
        $page->published = $request->published;
        $page->sidebar = 0;
        $page->save();

        return redirect()->route('admin.subscription.index')->withFlashSuccess(__('alerts.backend.general.updated'));


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function destroy($id)
    {
        
        $page = SubscribeCourse::findOrfail($id);
        $page->delete();
        return redirect()->route('admin.subscription.index')->withFlashSuccess(__('alerts.backend.general.deleted'));

    }



    /**
     * Delete all selected Page at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('page_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = SubscribeCourse::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Page from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('page_delete')) {
            return abort(401);
        }
        $page = Department::onlyTrashed()->findOrFail($id);
        $page->restore();

        return redirect()->route('admin.subscription.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Page from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('page_delete')) {
            return abort(401);
        }
        $page = Department::onlyTrashed()->findOrFail($id);
        $page->forceDelete();

        return redirect()->route('admin.subscription.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    public function updateStatus()
    {
        //dd('sfs');
        $teacher = SubscribeCourse::find(request('id'));
        $teacher->status = $teacher->status == 1? 0 : 1;

        $status = $teacher->save();
        if($teacher->status == 1) {
            DB::table('course_student')->insert([
                'course_id' => $teacher->course_id,
                'user_id' => $teacher->user_id,
                'rating' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        } else {
            DB::table('course_student')->where([
                'course_id' => $teacher->course_id,
                'user_id' => $teacher->user_id
               ])->delete();
        }
    }



}
