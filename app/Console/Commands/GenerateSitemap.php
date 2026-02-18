<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Course;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:sitemap {--chunk=}';
    protected $c = 1;
    protected $bd = 1;
    protected $bl = 1;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used to generate sitemap';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', -1);

        $sitemap = App::make("sitemap");
        $chunk_size = $this->option('chunk');
        if ($chunk_size == null) {
            $chunk_size = config('sitemap.chunk');
            if ($chunk_size == null) {
                $chunk_size = 100;
            }
        }
        //==========================Creating index for Courses===================================//
        $course_count = Course::where('published', '=', 1)->select('id')->count();
        $course_sitemap_ctr = ceil($course_count / $chunk_size);
        for ($i = 1; $i <= $course_sitemap_ctr; $i++) {

            $sitemap->addSitemap(env('APP_URL') . '/sitemap-' . Str::slug(config('app.name')) . '/' . 'course-list-' . $i . '.xml', date('Y-m-d H:i:s'));
        }
        $content = $sitemap->render('sitemapindex');
        Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/sitemap-index.xml', $content->original);


        //==========================Creating index for Bundles===================================//
        $bundle_count = Bundle::where('published', '=', 1)->select('id')->count();
        $bundle_sitemap_ctr = ceil($bundle_count / $chunk_size);
        for ($i = 1; $i <= $bundle_sitemap_ctr; $i++) {

            $sitemap->addSitemap(env('APP_URL') . '/sitemap-' . Str::slug(config('app.name')) . '/' . 'bundle-list-' . $i . '.xml', date('Y-m-d H:i:s'));
        }
        $content = $sitemap->render('sitemapindex');
        Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/sitemap-index.xml', $content->original);


        //==========================Creating index for Blog===================================//
        $blog_count = Blog::select('id')->count();
        $blog_sitemap_ctr = ceil($blog_count / $chunk_size);
        for ($i = 1; $i <= $blog_sitemap_ctr; $i++) {

            $sitemap->addSitemap(env('APP_URL') . '/sitemap-' . Str::slug(config('app.name')) . '/' . 'blog-list-' . $i . '.xml', date('Y-m-d H:i:s'));
        }
        $content = $sitemap->render('sitemapindex');
        Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/sitemap-index.xml', $content->original);


        //============================Creating sitemap for Course======================================//

        $this->c = 1;
        Course::where('published', '=', 1)->where('published', '=', 1)->chunk($chunk_size, function ($courses_chunks) {
            $images = "";
            $sitemap_courses = App::make("sitemap");
            foreach ($courses_chunks as $course) {

                $sitemap_courses->add(env('APP_URL') . '/course/' . $course->slug, date('Y-m-d H:i:s'), 0.8, 'daily', $images);
            }
            $content = $sitemap_courses->render('xml');

            Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/course-list-' . $this->c . '.xml', $content->original);
            $this->c = intval($this->c) + 1;
        });


        //====================Creating sitemap for Bundle =============================//

        $this->bd = 1;
        Bundle::where('published', '=', 1)->chunk($chunk_size, function ($bundles_chunks) {
            $images = "";
            $sitemap_bundles = App::make("sitemap");
            foreach ($bundles_chunks as $bundle) {

                $sitemap_bundles->add(env('APP_URL') . '/bundle/' . $bundle->slug, date('Y-m-d H:i:s'), 0.8, 'daily', $images);
            }
            $content = $sitemap_bundles->render('xml');

            Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/bundle-list-' . $this->bd . '.xml', $content->original);
            $this->bd = intval($this->bd) + 1;
        });

        //====================Creating sitemap for Blog =============================//

        $this->bl = 1;
        Blog::chunk($chunk_size, function ($blogs_chunks) {
            $images = "";
            $sitemap_blogs = App::make("sitemap");
            foreach ($blogs_chunks as $blog) {

                $sitemap_blogs->add(env('APP_URL') . '/blog/' . $blog->slug . '-' . $blog->id, date('Y-m-d H:i:s'), 0.8, 'daily', $images);
            }
            $content = $sitemap_blogs->render('xml');

            Storage::disk('local')->put('sitemap-' . Str::slug(config('app.name')) . '/blog-list-' . $this->bl . '.xml', $content->original);
            $this->bl = intval($this->bl) + 1;
        });


        Log::info('Sitemap Generated Successfully');
    }
}
