<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $contact_data = array(
            0 =>
                array(
                    'name' => 'short_text',
                    'value' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet ipsum dolor sit amet, consectetuer adipiscing elit.',
                    'status' => 1,
                ),
            1 =>
                array(
                    'name' => 'primary_address',
                    'value' => 'Hyderabad, India',
                    'status' => 1,
                ),
            2 =>
                array(
                    'name' => 'secondary_address',
                    'value' => 'Hyderabad, India',
                    'status' => 1,
                ),
            3 =>
                array(
                    'name' => 'primary_phone',
                    'value' => '(+91) 8686700634',
                    'status' => 1,
                ),
            4 =>
                array(
                    'name' => 'secondary_phone',
                    'value' => '(+91) 9989317437',
                    'status' => 1,
                ),
            5 =>
                array(
                    'name' => 'primary_email',
                    'value' => 'info@tadreeblms.com',
                    'status' => 1,
                ),
            6 =>
                array(
                    'name' => 'secondary_email',
                    'value' => '',
                    'status' => 1,
                ),
            7 =>
                array(
                    'name' => 'location_on_map',
                    'value' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d101408.2330017279!2d-122.15130702796371!3d37.41330279145996!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x808fb7495bec0189%3A0x7c17d44a466baf9b!2sMountain+View%2C+CA%2C+USA!5e0!3m2!1sen!2sin!4v1553663251022" width="800" height="600" frameborder="0" style="border:0" allowfullscreen></iframe>',
                    'status' => 1,
                ),
        );

        $footer_data = '{"short_description":{"text":"We take our mission of increasing global access to quality education seriously. We connect learners to the best universities and institutions from around the world.","status":1},"section1":{"type":"2","status":1},"section2":{"type":"3","status":1},"section3":{"type":"4","status":1},"social_links":{"status":1,"links":[{"icon":"fab fa-facebook-f","link":"#"},{"icon":"fab fa-instagram","link":"#"},{"icon":"fab fa-twitter","link":"#"}]},"newsletter_form":{"status":1},"bottom_footer":{"status":1},"copyright_text":{"text":"All right reserved  © 2018","status":1},"bottom_footer_links":{"status":1,"links":[{"label":"Privacy Policy","link":"' . asset('privacy-policy') . '"}]}}';
        $contact_data = json_encode($contact_data);
        $sections_layout1 = json_encode(config('sections.layout_1'));
        $sections_layout2 = json_encode(config('sections.layout_2'));
        $sections_layout3 = json_encode(config('sections.layout_3'));
        $sections_layout4 = json_encode(config('sections.layout_4'));
        $data = [
            'theme_layout' => 1,
            'font_color' => 'default',
            'layout_type' => 'wide',
            'layout_1' => $sections_layout1,
            'layout_2' => $sections_layout2,
            'layout_3' => $sections_layout3,
            'layout_4' => $sections_layout4,
            'counter' => '1',
            'total_students' => '1M+',
            'total_courses' => '1K+',
            'total_teachers' => '200+',
            'logo_b_image' => 'logo-black-text.png',
            'logo_w_image' => 'logo-white-text.png',
            'logo_white_image' => 'logo-white-image.png',
            'logo_popup' => 'popup-logo.png',
            'favicon_image' => 'popup-logo.png',
            'contact_data' => $contact_data,
            'footer_data' => $footer_data,
            'app__locale' => 'en',
            'app__display_type' => 'ltr',
            'app__currency' => 'USD',
            'lesson_timer' => 0,
            'show_offers' => 1,
            'access.captcha.registration' => 0,
            'sitemap.chunk' => 500,
            'one_signal' => 0,
            'our_mission' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet ipsum dolor sit amet, consectetuer adipiscing elit.',
            'our_vision' => 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet ipsum dolor sit amet, consectetuer adipiscing elit.',
            'landing_page_toggle' => 1
        ];

        foreach ($data as $key => $value) {
            $key = str_replace('__', '.', $key);
            $config = \App\Models\Config::firstOrCreate(['key' => $key]);
            $config->value = $value;
            $config->save();
        }
    }
}
