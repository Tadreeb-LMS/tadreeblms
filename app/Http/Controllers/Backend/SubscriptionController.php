<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Stripe\StripePlan;
use App\Models\Stripe\SubscribeCourse;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Auth;

class SubscriptionController extends Controller
{

    public function show_list()
    {
        $user_id = Auth::user()->id;
       $pages = SubscribeCourse::where('user_id',$user_id)->get();
        //dd($pages);
        // Show the page
        return view('backend.subscription', compact('pages'));

    }

    public function restore($id)
    {
        $subscription = SubscribeCourse::where('user_id', Auth::user()->id)
            ->onlyTrashed()
            ->findOrFail($id);

        $subscription->restore();

        return redirect()->route('user.subscriptions', ['show_deleted' => 1])
            ->withFlashSuccess('Subscription restored successfully.');
    }

    public function getData(Request $request)
    {
        $user_id = Auth::user()->id;

        if (request('show_deleted') == 1) {
            $pages = SubscribeCourse::where('user_id', $user_id)->onlyTrashed()->orderBy('created_at', 'desc');
        } else {
            $pages = SubscribeCourse::where('user_id', $user_id)->orderBy('created_at', 'desc');
        }

        return DataTables::of($pages)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($request) {
                if ($request->show_deleted == 1) {
                    return '<div class="table-actions">'
                        . '<form action="' . route('user.subscriptions.restore', ['subscription' => $q->id]) . '" method="POST" style="display:inline;">'
                        . csrf_field()
                        . '<button type="submit" class="btn-theme" title="Restore"><i class="fa fa-recycle"></i></button>'
                        . '</form>'
                        . '</div>';
                }

                $view = '<div class="table-actions">';
                if ($q->course_id) {
                    $view .= '<a href="' . route('courses.show', ['slug' => optional($q->course)->slug ?? '#']) . '" class="btn-theme" title="View Course"><i class="fa fa-eye"></i></a>';
                }
                $view .= '</div>';

                return $view;
            })

            ->editColumn('image', function ($q) {
                return ($q->image != null) ? '<img height="50px" src="' . asset('storage/uploads/' . $q->image) . '">' : 'N/A';
            })
            ->editColumn('course_id', function ($q) {
                return isset($q->course->title) ? $q->course->title : '-';
            })
            ->editColumn('user_name', function ($q) {
                return $q->user->first_name;
            })
            ->editColumn('email', function ($q) {
                return $q->user->email;
            })
            /*
            ->addColumn('status', function ($q) {
                $html = html()->label(html()->checkbox('')->id($q->id)
                ->checked(($q->status == 1) ? true : false)->class('switch-input')->attribute('data-id', $q->id)->value(($q->status == 1) ? 1 : 0).'<span class="switch-label"></span><span class="switch-handle"></span>')->class('switch switch-lg switch-3d switch-primary');
                return $html;
            })
            */
            ->addColumn('status', function ($q) {
               return $q->status ? 'Active' : 'NotActive';
            })
            ->addColumn('created', function ($q) {
                return $q->created_at->diffforhumans();
            })
            ->rawColumns(['image','course_id','user_name','email', 'actions','status'])
            ->make();
    }

    public function __invoke(Request $request)
    {
        $user     = $request->user();
        $invoices = $user->subscribed('default') ? $user->StripeInvoices() : optional();
        $activePlan = $user->subscribed('default') ? StripePlan::where('plan_id', $user->subscription()->stripe_plan)->first()??optional() : optional();
        return view('backend.subscription', compact('user', 'invoices', 'activePlan'));
    }

    /**
     * Download an invoice
     */
    public function downloadInvoice($invoiceId)
    {
        return auth()->user()->downloadInvoice($invoiceId, [
            'vendor'  => config('app.name'),
            'product' => 'Monthly Subscription'
        ]);
    }

    /**
     * Delete subscription
     */
    public function deleteSubscription(Request $request)
    {
        $user = $request->user();

        // cancel the subscription
        $user->subscription('default')->cancel();

        return redirect()->back()->withFlashSuccess(__('labels.subscription.cancel'));
    }

    /**
     * Update the credit card
     */
    public function updateCard(Request $request)
    {
        // get the user
        $user = $request->user();

        // get the cc token
        $ccToken = $request->input('cc_token');

        // update the card
        $user->updateCard($ccToken);

        // return a redirect back to account
        return redirect('account')->with(['success' => 'Credit card updated.']);
    }

}
