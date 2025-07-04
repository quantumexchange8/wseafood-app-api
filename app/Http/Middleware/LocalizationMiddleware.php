<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Session;

class LocalizationMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $available_locales = config('app.available_locales', ['en']);

        // 1️⃣ Prioritize Accept-Language header
        $locale = $request->header('Accept-Language');

        // 2️⃣ If not provided, fallback to session (optional for browsers)
        if (!$locale) {
            $locale = Session::get('locale', config('app.locale'));
        }

        // 3️⃣ Validate locale
        if (!in_array($locale, $available_locales)) {
            $locale = config('app.locale');
        }

        // 4️⃣ Set locale
        App::setLocale($locale);

        return $next($request);
    }
}
