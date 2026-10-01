<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreCategoriesRequest;
use App\Http\Requests\Admin\UpdateCategoriesRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class CategoriesController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!Gate::allows('category_access')) {
            return abort(401);
        }

        return view('backend.categories.index');
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
        $categories = "";


        if (request('show_deleted') == 1) {
            if (!Gate::allows('category_delete')) {
                return abort(401);
            }
            $categories = Category::query()->onlyTrashed()
                ->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        } else {
            $categories = Category::query()->orderBy('created_at', 'desc')->orderBy('id', 'desc');
        }

        if (auth()->user()->can('category_view')) {
            $has_view = true;
        }
        if (auth()->user()->can('category_edit')) {
            $has_edit = true;
        }
        if (auth()->user()->can('category_delete')) {
            $has_delete = true;
        }

        return DataTables::of($categories)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')
                        ->with([
                            'route_label' => 'admin.categories',
                            'label' => 'id',
                            'value' => $q->id
                        ]);
                }

                $allow_delete = false;

                if ($has_delete) {
                    $data = $q->courses->count() + $q->blogs->count();

                    if ($data == 0) {
                        $allow_delete = true;
                    }
                }

                // Start actions
                $actions = '<div class="action-pill">';

                if ($has_edit) {
                    $actions .= '<a title="Edit" class="" href="' .
                        route('admin.categories.edit', ['category' => $q->id]) .
                        '">
                        <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>';
                }

                if ($has_delete) {
                    if ($allow_delete) {
                        // Normal delete action
                        $actions .= view('backend.datatable.action-delete')
                            ->with([
                                'route' => route('admin.categories.destroy', ['category' => $q->id]),
                                'allow_delete' => true
                            ])
                            ->render();
                    } else {
                        // Category cannot be deleted because it has dependencies
                        if ($q->courses->count() > 0) {
                            $deleteMessage = 'This category cannot be deleted because it is assigned to one or more courses. Please reassign or remove the associated courses before deleting this category.';
                        } else {
                            $deleteMessage = 'This category cannot be deleted because it is associated with other records.';
                        }

                
                        $actions .= '<a title="Delete"
                            href="javascript:void(0);"
                            class="delete_warning"
                            data-delete-message="' . e($deleteMessage) . '"
                            style="cursor:pointer;">
                            <i class="fa fa-trash" aria-hidden="true"></i>
                        </a>';
                    }
                }

                // Link to courses
                $actions .= '<a title="Courses" class="" href="' .
                    route('admin.courses.index', ['cat_id' => $q->id]) .
                    '">
                    <i class="fa fa-address-book" aria-hidden="true"></i>
                </a>';

                $actions .= '</div>';

                return $actions;
            })
            // ->editColumn('icon', function ($q) {
            //     if ($q->icon != "") {
            //         return '<i style="font-size:40px;" class="'.$q->icon.'"></i>';
            //     } else {
            //         return 'N/A';
            //     }
            // })
            ->addColumn('courses', function ($q) {
                return $q->courses->count();
            })
            ->addColumn('blogs', function ($q) {
                return $q->blogs->count();
            })
            ->addColumn('status', function ($q) {
                return ($q->status == 1) ? "Enabled" : "Disabled";
            })
            ->rawColumns(['actions', 'icon'])
            ->make();
    }

    /**
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!Gate::allows('category_create')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofTeacher()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        return view('backend.categories.create', compact('courses', 'lessons'));
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreCategorysRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCategoriesRequest $request)
    {
        $this->validate($request, [
            'name' => 'required',
        ]);

        if (!Gate::allows('category_create')) {
            return abort(401);
        }
        $category = Category::where('slug', '=', Str::slug($request->name))->first();
        if ($category == null) {
            $category = new  Category();
        }
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->icon = $request->icon;
        $category->save();

        return redirect()->route('admin.categories.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }


    /**
     * Quickly create a category from the Create Course modal.
    */
    public function quickStore(Request $request)
    {
        if (!Gate::allows('category_create')) {
            return abort(401);
        }
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:191',
            ],
        ]);
        $slug = Str::slug($data['name']);
        $category = Category::firstOrCreate(
            [
                'slug' => $slug,
            ],
            [
                'name' => $data['name'],
            ]
        );
        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'category' => [
                'id' => $category->id,
                'text' => $category->name,
            ],
        ], 201);
    }
    
    /**
     * Show the form for editing Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!Gate::allows('category_edit')) {
            return abort(401);
        }
        $courses = \App\Models\Course::ofTeacher()->get();
        $courses_ids = $courses->pluck('id');
        $courses = $courses->pluck('title', 'id')->prepend('Please select', '');
        $lessons = \App\Models\Lesson::whereIn('course_id', $courses_ids)->get()->pluck('title', 'id')->prepend('Please select', '');

        $category = Category::findOrFail($id);

        return view('backend.categories.edit', compact('category', 'courses', 'lessons'));
    }

    /**
     * Update Category in storage.
     *
     * @param  \App\Http\Requests\UpdateCategorysRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCategoriesRequest $request, $id)
    {
        if (!Gate::allows('category_edit')) {
            return abort(401);
        }

        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->icon = $request->icon;
        $category->save();

        return redirect()->route('admin.categories.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }


    /**
     * Display Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!Gate::allows('category_view')) {
            return abort(401);
        }
        $category = Category::findOrFail($id);

        return view('backend.categories.show', compact('category'));
    }


    /**
     * Remove Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }

        $category = Category::findOrFail($id);

        if ($this->categoryHasDependencies($category)) {
            return redirect()
                ->route('admin.categories.index')
                ->withFlashDanger($this->categoryDeleteBlockedMessage());
        }

        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Delete all selected Category at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }

        if ($request->input('ids')) {
            $entries = Category::whereIn('id', $request->input('ids'))->get();

            foreach ($entries as $entry) {
                if ($this->categoryHasDependencies($entry)) {
                    return redirect()
                        ->route('admin.categories.index')
                        ->withFlashDanger($this->categoryDeleteBlockedMessage());
                }

                $entry->delete();
            }
        }

        return redirect()
            ->route('admin.categories.index')
            ->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }


    /**
     * Restore Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return redirect()->route('admin.categories.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    }

    /**
     * Permanently delete Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function perma_del($id)
    {
        if (!Gate::allows('category_delete')) {
            return abort(401);
        }
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->forceDelete();

        return redirect()->route('admin.categories.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
    private function categoryHasDependencies(Category $category): bool
    {
        return $category->courses()->exists() || $category->blogs()->exists();
    }

    private function categoryDeleteBlockedMessage(): string
    {
        return 'This category cannot be deleted because it is assigned to one or more courses. Please reassign or remove the associated courses before deleting this category.';
    }
}
