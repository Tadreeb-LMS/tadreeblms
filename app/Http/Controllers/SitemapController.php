<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SitemapController extends Controller
{

    public function index($file=null)
    {
        try{
            return Storage::disk('local')->get('sitemap-'.Str::slug(config('app.name')).'/'.$file);
        }
        catch (\Exception $e){
            abort(404);
        }
    }


    public function getIndex(){
        $course = Course::select('id')->count();
        $bundle = Bundle::select('id')->count();
        $blog = Blog::select('id')->count();

        return view('admin.generate-sitemap');
    }

    public function createIndex(Request $request)
    {
        ini_set('memory_limit', -1);
        $sitemap = App::make("sitemap");
        if($request->quotes_file_count == 0 || $request->quotes_file_count == "" || $request->topics_file_count == 0 || $request->topics_file_count == ""){

            Session::flash('flash_message','Please add valid file count.');

        }
        else
        {
            ///==========================Creating index for Quotes===================================//
            $quote_count = Quotes::select('id')->count();
            $quote_sitemap_ctr = ceil($quote_count / 500);
            for ($i = 1; $i <= $request->quotes_file_count; $i++) {

                $sitemap->addSitemap(env('APP_URL') . '/sitemap-' . env('APP_SHORT_NAME') . '/' . 'quotes-list-' . $i . '.xml', date('Y-m-d H:i:s'));
            }
            $content = $sitemap->render('sitemapindex');
            Storage::disk('local')->put('sitemap-' . env('APP_SHORT_NAME') . '/sitemap-index.xml', $content->original);


            //==========================Creating index for Topic-Author-Categories======================//
            $tca_count = TopicAuthorCategory::select('id')->count();
            $tca_sitemap_ctr = ceil($tca_count / 500);
            for ($i = 1; $i <= $request->topics_file_count; $i++) {

                $sitemap->addSitemap(env('APP_URL') . '/sitemap-' . env('APP_SHORT_NAME') . '/' . 'topic-quotes-' . $i . '.xml', date('Y-m-d H:i:s'));
            }
            $content = $sitemap->render('sitemapindex');
            Storage::disk('local')->put('sitemap-' . env('APP_SHORT_NAME') . '/sitemap-index.xml', $content->original);

            Session::flash('flash_message','Sitemap Index FIle Created Successfully.');

        }

        return back();
    }



}
