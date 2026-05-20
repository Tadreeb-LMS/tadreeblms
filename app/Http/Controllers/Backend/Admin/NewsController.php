<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreReasonsRequest;
use App\Http\Requests\Admin\UpdateReasonsRequest;
use App\Models\{Reason, News};
use CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use DB;

class NewsController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Reason.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('backend.news.index');
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
        $reasons = "";

        News::populateSlugs();

        $reasons = DB::table('news_update')->orderBy('created_at', 'desc')->where('lang', 'en')->get();

        $has_view = true;
        $has_edit = true;
        $has_delete = true;

        return DataTables::of($reasons)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.news', 'label' => 'news', 'value' => $q->id]);
                }

                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.news.edit', ['page' => $q->id]) . "?slug=$q->slug"])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.news.destroy', ['page' => $q->id])])
                        ->render();
                    $view .= $delete;
                }


                return $view;
            })
            ->editColumn('icon', function ($q) {
                if ($q->icon != "") {
                    return '<img width="100" height="100" src="' . asset('storage/uploads/' . $q->icon) . '" class="w-100">';
                } else {
                    return 'N/A';
                }
            })
            ->editColumn('status', function ($q) {
                // $html = html()->label(html()->checkbox('')->id($q->id)
                //     ->checked(($q->status == 1) ? true : false)->class('switch-input')->attribute('data-id', $q->id)->value(($q->status == 1) ? 1 : 0) . '<span class="switch-label"></span><span class="switch-handle"></span>')->class('switch switch-lg switch-3d switch-primary');
                $checked = $q->status == 1 ? 'checked' : '';
                $html = '<label class="switch switch-lg switch-3d switch-primary">
                            <input type="checkbox" id="' . $q->id .'" class="switch-input" data-id="' . $q->id .'" value="1" ' . $checked . '>
                            <span class="switch-label"></span>
                            <span class="switch-handle"></span>
                        </label>
                        ';
                return $html;
            })
            ->rawColumns(['actions', 'icon', 'status'])
            ->make();
    }

    /**
     * Show the form for creating new Reason.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.news.create');
    }

    /**
     * Store a newly created Reason in storage.
     *
     * @param  \App\Http\Requests\StoreReasonsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
        ]);

        $reason = new  News();
        $reason->title = $request->title;
        $reason->content = $request->get('content');
        if ($request->news_image != "") {
            $request = $this->saveFiles($request);
            $reason->icon = $request->news_image;
        }
        $reason->slug = CustomHelper::toSnakeCase($request->title);
        $reason->lang = $request->lang;
        $reason->save();

        return redirect()->route('admin.news.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Reason.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $lang = request()->lang ?? 'en';
        $slug = request()->slug;
        $reason = News::where('slug', $slug)->where('lang', $lang)->first();

        if (!$reason) {
            $existing = News::where('slug', $slug)->where('lang', 'en')->first();
            $reason = new News;
            $reason->lang = $lang;
            $reason->slug = CustomHelper::toSnakeCase($existing->title);
            $reason->save();
        }

        return view('backend.news.edit', compact('reason'));
    }

    /**
     * Update Reason in storage.
     *
     * @param  \App\Http\Requests\UpdateReasonsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


        $reason = News::findOrFail($id);
        $reason->title = $request->title;
        $reason->content = $request->get('content');

        
        if ($request->news_image != "") {
            $request = $this->saveFiles($request);
            $reason->icon = $request->news_image;
        }

        $reason->save();

        return redirect()->route('admin.news.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Reason.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $reason = News::findOrFail($id);

        return view('backend.news.show', compact('reason'));
    }


    /**
     * Remove Reason from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $reason = News::findOrFail($id);
        $reason->delete();

        return redirect()->route('admin.news.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Reason at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {

        if ($request->input('ids')) {
            $entries = News::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
        return redirect()->route('admin.news.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }


    public function status($id)
    {
        $reason = News::findOrFail($id);
        if ($reason->status == 1) {
            $reason->status = 0;
        } else {
            $reason->status = 1;
        }
        $reason->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    /**
     * Update reason status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     **/
    public function updateStatus()
    {
        $reason = News::findOrFail(request('id'));
        $reason->status = $reason->status == 1 ? 0 : 1;
        $reason->save();
        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully'
        ]);
    }
}
