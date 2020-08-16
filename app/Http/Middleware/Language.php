<?php

namespace App\Http\Middleware;

use Config;
use Closure;
use App;
use Illuminate\Support\Facades\Auth;

class Language
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check User login
        if (! Auth::check()) {
            return redirect()->route('login') ;
        }

        // 取得語系
        $language = ! empty($request->cookie("language")) ? $request->cookie("language") : Config::get("app.fallback_locale") ;

        // 語系
        App::setLocale($language) ;
        
        // 增加語系屬性至 request
        $request->request->add(["language" => $language]) ;

        return $next($request) ;
    }
}
