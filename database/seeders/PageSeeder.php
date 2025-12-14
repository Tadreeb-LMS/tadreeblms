<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $pagesTitles = ['About Us', 'Privacy Policy'];
        $content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed et urna eu risus ultrices sagittis. In tortor turpis, lobortis a tincidunt non, congue a lorem. Quisque imperdiet congue blandit. Cras quis tortor quis nunc porttitor pulvinar id id lacus. Curabitur dapibus augue orci. Praesent varius, dolor ut ultricies faucibus, ante nunc fringilla nulla, vitae egestas lorem erat eu libero. Praesent cursus tortor non gravida elementum. Praesent et arcu molestie, faucibus ligula sed, euismod urna. Praesent vitae orci metus. Nulla varius diam nec iaculis pulvinar. Sed mi enim, cursus nec urna a, interdum lobortis nisi.<br><br>

Mauris posuere sem at arcu commodo lobortis. Suspendisse sollicitudin dapibus congue. Etiam sit amet lacinia eros. In dictum lacinia tortor, nec mattis eros vulputate vel. Interdum et malesuada fames ac ante ipsum primis in faucibus. Donec posuere odio eget risus aliquam, quis ornare urna bibendum. Integer iaculis massa magna, et vehicula dui placerat a. Vestibulum ultricies mauris nunc, ut facilisis orci lobortis nec. Fusce cursus eget quam in elementum. Donec ipsum dui, semper ut commodo in, congue in urna.<br><br>
imperdiet congue blandit. Cras quis tortor quis nunc porttitor pulvinar id id lacus. Curabitur dapibus augue orci. Praesent varius, dolor ut ultricies faucibus, ante nunc fringilla nulla, vitae egestas lorem erat eu libero. Praesent cursus tortor non gravida elementum. Praesent et arcu molestie, faucibus ligula sed, euismod urna. Praesent vitae orci metus. Nulla varius diam nec iaculis pulvinar. Sed mi enim, cursus nec urna a, interdum lobortis nisi.';

        foreach ($pagesTitles as $item){
            $page = \App\Models\Page::where('slug','=',Str::slug($item))->first();
           $user_id = \App\Models\Auth\User::role('administrator')->first()->id;
            if($page == ""){
                $page = new \App\Models\Page();
            }
            $page->title = $item;
            $page->slug = Str::slug($item);
            $page->content = $content;
            $page->user_id = $user_id;
            $page->sidebar = 1;
            $page->published = 1;
            $page->save();
        }
    }
}
