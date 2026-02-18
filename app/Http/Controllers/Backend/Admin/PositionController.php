<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\StorePositionRequest;
use App\Http\Requests\Admin\UpdatePagesRequest;
use App\Models\Page;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Imports\DepartmentImport;
use App\Imports\PositionImport;
use App\Models\Position;
use Config;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;


class PositionController extends Controller
{
    use FileUploadTrait;
    private $tags;

    public function index()
    {
        if (!Gate::allows('page_access')) {
            return abort(401);
        }
        // Grab all the pages
        $pages = Position::all();
        //dd($pages);
        // Show the page
        return view('backend.position.index', compact('pages'));

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
            $pages = Position::onlyTrashed()->orderBy('created_at', 'desc')->get();

        } else {
            $pages = Position::orderBy('created_at', 'desc')->get();

        }


        if (auth()->user()->can('page_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('page_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('page_delete')) {
            $has_delete = true;
        }

        return DataTables::of($pages)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.position', 'label' => 'id', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.position.show', ['page' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.position.edit', ['page' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => url('user/position-destroy').'/'.$q->id])
                        ->render();
                    $view .= $delete;
                }

                return $view;

            })

            ->editColumn('image', function ($q) {
                return ($q->image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->image) . '">' : 'N/A';
            })
            ->addColumn('status', function ($q) {
                $text = "";
                $text = ($q->published == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >".trans('labels.backend.pages.fields.published')."</p>" : "<p class='text-dark mb-1 font-weight-bold text-center bg-light p-1 mr-1' >".trans('labels.backend.pages.fields.drafted')."</p>";

                return $text;
            })
            ->editColumn('content', function($row) {
                return trim(html_entity_decode(strip_tags($row->content)));
            })
            ->addColumn('created', function ($q) {
                return $q->created_at->diffforhumans();
            })
            ->rawColumns(['image', 'actions','status'])
            ->make();
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
        return view('backend.position.create');

    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StorePositionRequest $request)
    {
        //dd($request->all());
        $page = new Position();
        $page->title = $request->title;
        if($request->slug == ""){
            $page->slug = Str::slug($request->title);
        }else{
            $page->slug = $request->slug;
        }
        $page->content = $request->content;
        // $message = $request->get('content');
        // $dom = new \DOMDocument();
        // $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);


        // $page->content = $dom->saveHTML();


        $page->user_id = auth()->user()->id;
        $page->published = 1;
        $page->sidebar = 1;
        $page->save();

        return response()->json([ 'status'=>'success' , 'clientmsg' => 'Added successfully' ]);

        if ($page->id) {
            return redirect()->route('admin.position.index')->withFlashSuccess(__('alerts.backend.general.created'));
        } else {
            return redirect()->route('admin.position.index')->withFlashDanger(__('alerts.backend.general.error'));

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
        $page = Position::findOrFail($id);
        return view('backend.position.show', compact('page'));

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
        $page = Position::where('id', '=', $id)->first();
        return view('backend.position.edit', compact('page'));

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

        $page = Position::findOrFail($id);
        $page->title = $request->title;
        if($request->slug == ""){
            $page->slug = Str::slug($request->title);
        }else{
            $page->slug = $request->slug;
        }
        $page->content = $request->content;
        // $message = $request->get('content');
        // libxml_use_internal_errors(true);
        // $dom = new \DOMDocument();
        // $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'));

        // $page->content = $dom->saveHTML();


        $page->meta_title = $request->meta_title;

        $page->published = $request->published;
        $page->sidebar = 0;
        $page->save();

        return redirect()->route('admin.position.index')->withFlashSuccess(__('alerts.backend.general.updated'));


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function destroy($id)
    {
     //   print_r();die;
        $page = Position::findOrfail($id);
        $page->delete();
        return redirect()->route('admin.position.index')->withFlashSuccess(__('alerts.backend.general.deleted'));

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
            $entries = Position::whereIn('id', $request->input('ids'))->get();

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
        $page = Position::onlyTrashed()->findOrFail($id);
        $page->restore();

        return redirect()->route('admin.position.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
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
        $page = Position::onlyTrashed()->findOrFail($id);
        $page->forceDelete();

        return redirect()->route('admin.position.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    public function import_exl(){
        // dd('hi');

        $IsSaved = false;

        if (request()->hasFile('file')) {

            $maximum_execution_time = Config::get('constants.maximum_execution_time');
            set_time_limit($maximum_execution_time);
            $IsDataSuccessfullyInserted = false;
            $ExcelData = Excel::toArray(new PositionImport,request()->file('file'));
            if(!empty($ExcelData)){
                $ExtractedDataFromExcel = $ExcelData[0];

                if(!empty($ExtractedDataFromExcel)){
                    $count = 0;

                    $TotalData = count($ExtractedDataFromExcel) - 0;
                    foreach($ExtractedDataFromExcel as $ExcelKey => $ExcelValue){

                        if($count == 0){
                            $count++;
                            continue;
                        }
                        $count++;
                        $IsDataSuccessfullyInserted = false;
                        $exist_slug = Position::where('slug',Str::slug(trim($ExcelValue[0])))->first();

                        if(empty($exist_slug)){
                        if($ExcelValue[1] != null){
                                $RetailerPlanId = 0;
                                $RetailerPlan = new Position();
                                $RetailerPlan->title = trim($ExcelValue[0]);
                                $RetailerPlan->slug = Str::slug(trim($ExcelValue[0]));
                                $message = trim($ExcelValue[1]);
                                $dom = new \DOMDocument();
                                $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
                                $RetailerPlan->content = $dom->saveHTML();
                                $RetailerPlan->user_id = auth()->user()->id;
                                $RetailerPlan->published = 1;
                                $RetailerPlan->sidebar = 1;
                                if($RetailerPlan->save()){
                                    $RetailerPlanId = $RetailerPlan->id;
                                    $IsDataSuccessfullyInserted = true;
                                }
                            if($IsDataSuccessfullyInserted){
                                $TotalData++;
                            }
                        }
                }
                else{

                    return redirect()->route('admin.position.index')->withFlashDanger('Title is already exist');
                }
            }
                }
            }
        }
        if($IsDataSuccessfullyInserted){
            return redirect()->route('admin.position.index')->withFlashSuccess(trans('alerts.backend.general.created'));
        }
        return redirect()->route('admin.position.index')->withFlashDanger('Something went wrong');

    }



}
