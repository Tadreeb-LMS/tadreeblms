<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Requests\Admin\StoreBundlesRequest;
use App\Http\Requests\Admin\UpdateBundlesRequest;
use App\Models\Auth\User;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseTimeline;
use function foo\func;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCoursesRequest;
use App\Http\Requests\Admin\UpdateCoursesRequest;
use App\Http\Controllers\Traits\FileUploadTrait;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class BundlesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('bundle_access')) {
            return abort(401);
        }


        return view('backend.bundles.index');
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
        $bundles = "";

        if (request('show_deleted') == 1) {
            if (!Gate::allows('bundle_delete')) {
                return abort(401);
            }
            $bundles = Bundle::query()->ofAuthor()->onlyTrashed()->orderBy('created_at', 'desc');
        } elseif (request('cat_id') != "") {
            $id = request('cat_id');
            $bundles = Bundle::query()->ofAuthor()->where('category_id', '=', $id)->orderBy('created_at', 'desc');
        } else {
            $bundles = Bundle::query()->ofAuthor()->orderBy('created_at', 'desc');
        }


        if (auth()->user()->can('bundle_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('bundle_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('bundle_delete')) {
            $has_delete = true;
        }

        return DataTables::of($bundles)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.bundles', 'label' => 'id', 'value' => $q->id]);
                }
                if ($has_view) {
                    $view = view('backend.datatable.action-view')
                        ->with(['route' => route('admin.bundles.show', ['bundle' => $q->id])])->render();
                }
                if ($has_edit) {
                    $edit = view('backend.datatable.action-edit')
                        ->with(['route' => route('admin.bundles.edit', ['bundle' => $q->id])])
                        ->render();
                    $view .= $edit;
                }

                if ($has_delete) {
                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.bundles.destroy', ['bundle' => $q->id])])
                        ->render();
                    $view .= $delete;
                }
                if ($q->published == 1) {
                    $type = 'action-unpublish';
                } else {
                    $type = 'action-publish';
                }

                $view .= view('backend.datatable.'.$type)
                    ->with(['route' => route('admin.bundles.publish', ['id' => $q->id])])->render();
                return $view;
            })
            ->editColumn('course_image', function ($q) {
                return ($q->course_image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->course_image) . '">' : 'N/A';
            })
            ->addColumn('courses', function ($q) {
                return $q->courses->count();
            })
            ->addColumn('status', function ($q) {
                $text = "";
                $text = ($q->published == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-dark p-1 mr-1' >" . trans('labels.backend.bundles.fields.published') . "</p>" : "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.courses.fields.unpublished') . "</p>";
                if (auth()->user()->isAdmin()) {
                    $text .= ($q->featured == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-warning p-1 mr-1' >" . trans('labels.backend.bundles.fields.featured') . "</p>" : "";
                    $text .= ($q->trending == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-success p-1 mr-1' >" . trans('labels.backend.bundles.fields.trending') . "</p>" : "";
                    $text .= ($q->popular == 1) ? "<p class='text-white mb-1 font-weight-bold text-center bg-primary p-1 mr-1' >" . trans('labels.backend.bundles.fields.popular') . "</p>" : "";
                }
                return $text;
            })
            ->editColumn('price', function ($q) {
                if ($q->free == 1) {
                    return trans('labels.backend.courses.fields.free');
                }
                return $q->price;
            })
            ->addColumn('category', function ($q) {
                return $q->category->name;
            })
            ->rawColumns(['course_image', 'actions', 'status'])
            ->make();
    }


    /**
     * Show the form for creating new Course.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('bundle_create')) {
            return abort(401);
        }

        $courses = Course::ofTeacher()->pluck('title', 'id');
        $categories = Category::where('status', '=', 1)->pluck('name', 'id');

        return view('backend.bundles.create', compact('courses', 'categories'));
    }

    /**
     * Store a newly created Course in storage.
     *
     * @param  \App\Http\Requests\StoreBundlesRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBundlesRequest $request)
    {
        if (!Gate::allows('bundle_create')) {
            return abort(401);
        }
        $request->all();

        $request = $this->saveFiles($request);
        $bundle = Bundle::create($request->all());
        if (($request->slug == "") || $request->slug == null) {
            $bundle->slug = Str::slug($request->title);
            $bundle->save();
        }
        if ((int)$request->price == 0) {
            $bundle->price = null;
            $bundle->save();
        }

        $bundle->user_id = auth()->user()->id;
        $bundle->save();

        $courses = array_filter((array)$request->input('courses'));
        $bundle->courses()->sync($courses);


        return redirect()->route('admin.bundles.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Show the form for editing Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('bundle_edit')) {
            return abort(401);
        }

        $courses = Course::ofTeacher()->pluck('title', 'id');


        $categories = Category::where('status', '=', 1)->pluck('name', 'id');
        $bundle = Bundle::findOrFail($id);

        return view('backend.bundles.edit', compact('bundle', 'courses', 'categories'));
    }

    /**
     * Update Course in storage.
     *
     * @param  \App\Http\Requests\UpdateCoursesRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBundlesRequest $request, $id)
    {
        if (!Gate::allows('bundle_edit')) {
            return abort(401);
        }
        $request = $this->saveFiles($request);
        $bundle = Bundle::findOrFail($id);

        $bundle->update($request->all());
        if (($request->slug == "") || $request->slug == null) {
            $bundle->slug = Str::slug($request->title);
            $bundle->save();
        }


        if ((int)$request->price == 0) {
            $bundle->price = null;
            $bundle->save();
        }

        $courses = array_filter((array)$request->input('courses'));
        $bundle->courses()->sync($courses);


        return redirect()->route('admin.bundles.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Course.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('bundle_view')) {
            return abort(401);
        }
        $bundle = Bundle::findOrFail($id);

        return view('backend.bundles.show', compact('bundle'));
    }


    /**
     * Remove Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('bundle_delete')) {
            return abort(401);
        }
        $bundle = Bundle::findOrFail($id);
        if ($bundle->students->count() >= 1) {
            return redirect()->route('admin.bundles.index')->withFlashDanger(trans('alerts.backend.general.delete_warning_bundle'));
        } else {
            $bundle->delete();
        }

        return redirect()->route('admin.bundles.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Course at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('bundle_delete')) {
            return abort(401);
        }
        if ($request->input('ids')) {
            $entries = Bundle::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                $entry->delete();
            }
        }
    }


    /**
     * Restore Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('bundle_delete')) {
            return abort(401);
        }
        $bundle = Bundle::onlyTrashed()->findOrFail($id);
        $bundle->restore();

        return redirect()->route('admin.bundles.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Course from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('bundle_delete')) {
            return abort(401);
        }
        $bundle = Bundle::onlyTrashed()->findOrFail($id);
        $bundle->forceDelete();

        return redirect()->route('admin.bundles.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }


    /**
     * Publish / Unpublish courses
     *
     * @param  Request
     */
    public function publish($id)
    {
        if (!Gate::allows('bundle_edit')) {
            return abort(401);
        }

        $bundle = Bundle::findOrFail($id);
        if ($bundle->published == 1) {
            $bundle->published = 0;
        } else {
            $bundle->published = 1;
        }
        $bundle->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }
}
