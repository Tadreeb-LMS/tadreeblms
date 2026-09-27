<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreDepartmentRequest;
use App\Http\Requests\Admin\UpdatePagesRequest;
use App\Models\Page;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use App\Imports\DepartmentImport;
use Config;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;
use App\Exports\DepartmentTemplateExport;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    use FileUploadTrait;
    private $tags;

    public function index()
    {
        if (!Gate::allows('page_access')) {
            return abort(401);
        }
        // Grab all the pages
        $pages = Department::all();
        //dd($pages);
        // Show the page
        return view('backend.department.index', compact('pages'));

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
            $pages = Department::onlyTrashed()->orderBy('created_at', 'desc')->get();

        } else {
            $pages = Department::orderBy('created_at', 'desc')->get();

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
    if ($request->show_deleted == 1) {
        return view('backend.datatable.action-trashed')
            ->with(['route_label' => 'admin.department', 'label' => 'id', 'value' => $q->id]);
    }

    $actions = '<div class="action-pill">';

    if ($has_view) {
        $actions .= '<a title="View" class="" href="' . route('admin.department.show', ['page' => $q->id]) . '">
                         <i class="fa fa-eye" aria-hidden="true"></i>
                    </a>';
    }

    if ($has_edit) {
        $actions .= '<a title="Edit" class="" href="' . route('admin.department.edit', ['page' => $q->id]) . '">
                         <i class="fa fa-edit" aria-hidden="true"></i>
                    </a>';
    }

    if ($has_delete) {
        $actions .= view('backend.datatable.action-delete')
            ->with(['route' => url('user/department-destroy') . '/' . $q->id])
            ->render();
    }

    $actions .= '</div>';

    return $actions;
})

            ->editColumn('image', function ($q) {
                return ($q->image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->image) . '">' : 'N/A';
            })
            ->addColumn('status', function ($q) {
                $text = "";
                $text = ($q->published == 1) ? "<p class='pill-publish' >".trans('labels.backend.pages.fields.published')."</p>" : "<p class='pill-draft' >".trans('labels.backend.pages.fields.drafted')."</p>";

                return $text;
            })
            ->addColumn('created', function ($q) {
                return $q->created_at ? $q->created_at->diffforhumans() : '-';
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
        return view('backend.department.create');

    }


    public function downloadTemplate()
    {
        return Excel::download(
            new DepartmentTemplateExport(),
            'user-group-import-template.xlsx'
        );
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
        $page->content = $request->content;
        // $message = $request->get('content');
        // $dom = new \DOMDocument();
        // $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);


        // $page->content = $dom->saveHTML();


        $page->user_id = auth()->user()->id;
        $page->published = 1;
        $page->sidebar = 1;
        $page->save();

        session()->flash('success', 'User Group created successfully.');

        return response()->json([
            'status' => 'success',
            'redirect' => route('admin.department.index'),
        ]);
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
        return view('backend.department.show', compact('page'));

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
        return view('backend.department.edit', compact('page'));

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
        $page->content = $request->content;
        // $message = $request->get('content');
        // libxml_use_internal_errors(true);
        // $dom = new \DOMDocument();
        // $dom->loadHtml(mb_convert_encoding($message,  'HTML-ENTITIES', 'UTF-8'));

        // $page->content = $dom->saveHTML();


        $page->meta_title = $request->meta_title;
        $page->published = 1;
        $page->sidebar = 0;
        $page->save();

        return redirect()->route('admin.department.index')->with('success', 'User Group updated successfully.');


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
        $page = Department::findOrfail($id);
        $page->delete();
        return redirect()->route('admin.department.index')->withFlashSuccess(__('alerts.backend.general.deleted'));

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
            $entries = Department::whereIn('id', $request->input('ids'))->get();

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

        return redirect()->route('admin.department.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
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

        return redirect()->route('admin.department.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    public function import_exl(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'extensions:xlsx,xls',
                'max:10240',
            ],
        ], [
            'file.required' => 'Please select a User Group import file.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'Invalid file format. Please upload a supported User Group import file.',
            'file.extensions' => 'Invalid file format. Please upload a supported User Group import file.',
            'file.max' => 'The User Group import file may not be larger than 10 MB.',
        ]);

        $file = $request->file('file');

        if (!$file || !$file->isValid()) {
            return redirect()
                ->route('admin.department.index')
                ->withFlashDanger('The uploaded file is invalid. Please select a valid User Group import file.');
        }

        $maximum_execution_time = Config::get('constants.maximum_execution_time');
        set_time_limit($maximum_execution_time);

        try {
            /*
            * Parse the file only after server-side validation has confirmed
            * that it is an Excel file.
            */
            $excelData = Excel::toArray(
                new DepartmentImport,
                $file
            );

            $rows = $excelData[0] ?? [];

            if (empty($rows)) {
                return redirect()
                    ->route('admin.department.index')
                    ->withFlashDanger(
                        'Invalid User Group import file. The file is empty or contains no data.'
                    );
            }

            /*
            * Validate the expected import structure.
            * The User Group template contains a "title" column.
            */
            $header = $rows[0] ?? [];

            if (
                !is_array($header) ||
                strtolower(trim((string) ($header[0] ?? ''))) !== 'title'
            ) {
                return redirect()
                    ->route('admin.department.index')
                    ->withFlashDanger(
                        'Invalid User Group import file. Please use the provided User Group import template.'
                    );
            }

            $rowsToImport = [];
            $seenSlugs = [];

            foreach (array_slice($rows, 1) as $rowIndex => $row) {
                $excelRowNumber = $rowIndex + 2;

                if (!is_array($row)) {
                    return redirect()
                        ->route('admin.department.index')
                        ->withFlashDanger(
                            "Invalid User Group import file. Invalid data found on row {$excelRowNumber}."
                        );
                }

                $hasData = false;

                foreach ($row as $value) {
                    if (trim((string) $value) !== '') {
                        $hasData = true;
                        break;
                    }
                }

                // Ignore completely empty rows.
                if (!$hasData) {
                    continue;
                }

                $title = trim((string) ($row[0] ?? ''));

                if ($title === '') {
                    return redirect()
                        ->route('admin.department.index')
                        ->withFlashDanger(
                            "Invalid User Group import file. User Group title is missing on row {$excelRowNumber}."
                        );
                }

                $slug = Str::slug($title);

                if ($slug === '') {
                    return redirect()
                        ->route('admin.department.index')
                        ->withFlashDanger(
                            "Invalid User Group import file. Invalid User Group title on row {$excelRowNumber}."
                        );
                }

                /*
                * Prevent duplicate User Groups inside the same uploaded file.
                */
                if (isset($seenSlugs[$slug])) {
                    return redirect()
                        ->route('admin.department.index')
                        ->withFlashDanger(
                            "Duplicate User Group '{$title}' found in the import file."
                        );
                }

                $seenSlugs[$slug] = true;

                /*
                * Prevent duplicate User Groups already existing in the database.
                */
                if (Department::where('slug', $slug)->exists()) {
                    return redirect()
                        ->route('admin.department.index')
                        ->withFlashDanger(
                            "User Group '{$title}' already exists."
                        );
                }

                $rowsToImport[] = [
                    'title' => $title,
                    'slug' => $slug,
                ];
            }

            if (empty($rowsToImport)) {
                return redirect()
                    ->route('admin.department.index')
                    ->withFlashDanger(
                        'Invalid User Group import file. No valid User Group records were found.'
                    );
            }

            /*
            * Save all records inside a transaction so a database error does
            * not leave the import partially completed.
            */
            DB::transaction(function () use ($rowsToImport) {
                foreach ($rowsToImport as $row) {
                    $department = new Department();
                    $department->title = $row['title'];
                    $department->slug = $row['slug'];
                    $department->user_id = auth()->user()->id;
                    $department->published = 1;
                    $department->sidebar = 1;
                    $department->save();
                }
            });

            return redirect()
                ->route('admin.department.index')
                ->withFlashSuccess(
                    trans('alerts.backend.general.created')
                );

        } catch (\Throwable $e) {
            /*
            * Keep technical parser details out of the user-facing response.
            * Laravel will report the exception to the configured logger.
            */
            report($e);

            return redirect()
                ->route('admin.department.index')
                ->withFlashDanger(
                    'Unable to read the uploaded file. Please upload a valid User Group import file.'
                );
        }
    }


}
