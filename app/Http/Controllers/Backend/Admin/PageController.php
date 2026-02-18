<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StorePagesRequest;
use App\Http\Requests\Admin\UpdatePagesRequest;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;


class PageController extends Controller
{
    use FileUploadTrait;
    private $tags;

    public function index()
    {
        if (!Gate::allows('page_access')) {
            return abort(401);
        }
        // Grab all the pages
        $pages = Page::all();
        // Show the page
        return view('backend.pages.index', compact('pages'));
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
            $pages = Page::onlyTrashed()->orderBy('created_at', 'desc')->where('lang', 'en')->get();
        } else {
            $pages = Page::orderBy('created_at', 'desc')->where('lang', 'en')->get();
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
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.pages', 'label' => 'id', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.pages.show', ['page' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.pages.edit', ['page' => $q->id]) . "?slug=$q->slug"])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.pages.destroy', ['page' => $q->id])])
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
                $text = ($q->published == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.pages.fields.published') . "</p>" : "<p class='text-dark mb-1 font-weight-bold text-center bg-light p-1 mr-1' >" . trans('labels.backend.pages.fields.drafted') . "</p>";

                return $text;
            })
            ->addColumn('created', function ($q) {
                return $q->created_at->diffforhumans();
            })
            ->rawColumns(['image', 'actions', 'status'])
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
        return view('backend.pages.create');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(StorePagesRequest $request)
    {
        ini_set('memory_limit', '-1');
        if (!Gate::allows('page_create')) {
            return abort(401);
        }

        $page = new Page();
        $page->title = $request->title;
        if ($request->slug == "") {
            $page->slug = Str::slug($request->title);
        } else {
            $page->slug = $request->slug;
        }
        $message = $request->get('content');
        $dom = new \DOMDocument();
        $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $images = $dom->getElementsByTagName('img');

        // foreach <img> in the submitted message
        foreach ($images as $img) {

            $src = $img->getAttribute('src');
            // if the img source is 'data-url'
            if (preg_match('/data:image/', $src)) {
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                // Generating a random filename
                $filename = uniqid();
                $filepath = storage_path("/app/public/uploads/page/$filename.$mimetype");
                // @see http://image.intervention.io/api/
                $image = \Image::make($src)
                    // resize if required
                    /* ->resize(300, 200) */
                    ->encode($mimetype, 100) // encode file to the specified mimetype
                    ->save($filepath);
                $new_src = asset("storage/uploads/$filename.$mimetype");
                $dirname = dirname($filename);

                $img->removeAttribute('src');
                $img->setAttribute('src', $new_src);
            } // <!--endif
        } // <!-
        $page->content = $dom->saveHTML();

        $request = $this->saveFilesOptimize($request);
        $page->user_id = auth()->user()->id;
        $page->image = $request->featured_image;
        $page->meta_title = $request->meta_title;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;
        $page->published = $request->published;
        $page->sidebar = $request->sidebar;
        $page->save();



        if ($page->id) {
            return redirect()->route('admin.pages.index')->withFlashSuccess(__('alerts.backend.general.created'));
        } else {
            return redirect()->route('admin.pages.index')->withFlashDanger(__('alerts.backend.general.error'));
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
        $page = Page::findOrFail($id);
        return view('backend.pages.show', compact('page'));
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
        $lang = request()->lang ?? 'en';
        $slug = request()->slug;
        $page = Page::where('slug', '=', $slug)->where('lang', $lang)->first();
        if (!$page) {
            $page = new Page;
            $page->user_id = auth()->id();
            $page->lang = $lang;
            $page->slug = $slug;
            $page->save();
        }
        return view('backend.pages.edit', compact('page'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function update(UpdatePagesRequest $request, $id)
    {
        ini_set('memory_limit', '-1');
        if (!Gate::allows('page_edit')) {
            return abort(401);
        }
        $page = Page::findOrFail($id);
        $page->title = $request->title;
        if ($request->slug == "") {
            $page->slug = Str::slug($request->title);
        } else {
            $page->slug = $request->slug;
        }

        $message = $request->get('content');
        libxml_use_internal_errors(true);
        $dom = new \DOMDocument();
        $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'));
        $images = $dom->getElementsByTagName('img');
        // foreach <img> in the submited message
        foreach ($images as $img) {
            $src = $img->getAttribute('src');
            // if the img source is 'data-url'
            if (preg_match('/data:image/', $src)) {
                // get the mimetype
                preg_match('/data:image\/(?<mime>.*?)\;/', $src, $groups);
                $mimetype = $groups['mime'];
                // Generating a random filename
                $filename = uniqid();
                info($filename);
                $filepath = storage_path("/app/public/uploads/page/$filename.$mimetype");
                // @see http://image.intervention.io/api/
                $image = Image::make($src)
                    ->encode($mimetype, 100) // encode file to the specified mimetype
                    ->save($filepath);
                $new_src = asset("storage/uploads/$filename.$mimetype");
            } // <!--endif
            else {
                $new_src = $src;
            }
            $img->removeAttribute('src');
            $img->setAttribute('src', $new_src);
        } // <!
        //-
        $page->content = $dom->saveHTML();

        if ($request->featured_image != "") {
            
            $request = $this->saveFilesOptimize($request);
            $page->image = $request->featured_image;
        }
        $page->meta_title = $request->meta_title;
        $page->meta_keywords = $request->meta_keywords;
        $page->meta_description = $request->meta_description;
        $page->published = $request->published;
        $page->sidebar = $request->sidebar;
        $page->lang = $request->lang;
        $page->save();

        return redirect()->route('admin.pages.index')->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Page $page
     * @return Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('page_delete')) {
            return abort(401);
        }
        $page = Page::findOrfail($id);
        $page->delete();
        return redirect()->route('admin.pages.index')->withFlashSuccess(__('alerts.backend.general.deleted'));
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
            $entries = Page::whereIn('id', $request->input('ids'))->get();

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
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->restore();

        return redirect()->route('admin.pages.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
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
        $page = Page::onlyTrashed()->findOrFail($id);
        $page->forceDelete();

        return redirect()->route('admin.pages.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
