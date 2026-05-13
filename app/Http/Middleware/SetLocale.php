<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

/**
 * ตั้งค่าภาษา UI ตามลำดับ:
 * 1. ?lang=th|en (query string)
 * 2. user->preferred_language (ถ้า login)
 * 3. session('locale')
 * 4. config app.locale
 */
class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = config('app.locale', 'th');

        if ($request->has('lang') && in_array($request->get('lang'), ['th', 'en'], true)) {
            $locale = $request->get('lang');
            session(['locale' => $locale]);
        } elseif ($request->user() && in_array($request->user()->preferred_language, ['th', 'en'], true)) {
            $locale = $request->user()->preferred_language;
        } elseif ($request->session()->has('locale')) {
            $locale = $request->session()->get('locale');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
