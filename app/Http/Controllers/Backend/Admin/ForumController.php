<?php

namespace App\Http\Controllers\Backend\Admin;

use \App\Models\ChatterCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ForumController extends Controller
{
    /**
     * Display a listing of Taxes.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $forum_categories = ChatterCategory::orderBy('created_at', 'desc')
            ->get();
        return view('backend.forum-categories.index', compact('forum_categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $forum_categories = ChatterCategory::orderBy('created_at', 'desc')
            ->get()->pluck('name', 'id')->prepend('Please select', '');
        return view('backend.forum-categories.create', compact('forum_categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
            'order' => 'required',
        ]);

        $cat = ChatterCategory::where('slug', '=', Str::slug($request->name))->first();
        if ($cat == null) {
            $cat = new ChatterCategory();
            $cat->parent_id = $request->parent_id;
            $cat->name = $request->name;
            $cat->slug = Str::slug($request->name);
            $cat->order = $request->order;
            $cat->color = $request->color;
            $cat->save();
        }

        return redirect()->route('admin.forums-category.index')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $forum_categories = ChatterCategory::orderBy('created_at', 'desc')
            ->get()->pluck('name', 'id')->prepend('Please select', '');
        $forum_category = ChatterCategory::findOrFail($id);
        return view('backend.forum-categories.edit', compact('forum_category', 'forum_categories'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $forums_category)
    {

        $this->validate($request, [
            'name' => 'required',
            'color' => 'required',
            'order' => 'required',
        ]);

        $cat = ChatterCategory::where('id', '=', $forums_category)->first();

        $cat->parent_id = $request->parent_id;
        $cat->name = $request->name;
        $cat->slug = Str::slug($request->name);
        $cat->order = $request->order;
        $cat->color = $request->color;
        $cat->save();

        return redirect()->route('admin.forums-category.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tax = ChatterCategory::findOrFail($id);
        $tax->delete();
        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));

    }


    public function status($id)
    {
        $tax = ChatterCategory::findOrFail($id);
        if ($tax->status == 1) {
            $tax->status = 0;
        } else {
            $tax->status = 1;
        }
        $tax->save();

        return back()->withFlashSuccess(trans('alerts.backend.general.updated'));
    }
}
