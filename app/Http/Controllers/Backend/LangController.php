<?php

namespace App\Http\Controllers\Backend;
use Barryvdh\TranslationManager\Controller as Controller;
use App\Models\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class LangController extends Controller
{
    public function postAddLocale(Request $request)
    {
        $locales = $this->manager->getLocales();
        $newLocale = str_replace([], '-', trim($request->input('new-locale')));
        if (!$newLocale || in_array($newLocale, $locales)) {
            return redirect()->back();
        }
        $this->manager->addLocale($newLocale);
        $locale_data = Locale::where('short_name', '=', $newLocale)->first();
        if (!$locale_data) {
            $locale = new Locale();
            $locale->short_name = $newLocale;
            $locale->display_type = 'ltr';
            if (Schema::hasColumn('locales', 'is_enabled')) {
                $locale->is_enabled = 0;
            }
            $locale->save();
        }
        return redirect()->back();
    }

    public function postRemoveLocaleFolder(Request $request)
    {
        $locale = $request->delete_locale;
        if($locale){
            Locale::where('short_name', '=', $locale)->delete();
            $this->manager->removeLocale($locale);
            try{

              \Storage::disk('lang')->deleteDirectory($locale);


            }catch (\Exception $e){
                \Log::info($e->getMessage().' - Error while deleting locale "'.$locale.'"');
            }
            return redirect()->back();
        }else{
            abort(404);
        }

    }

}