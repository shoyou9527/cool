<?php
namespace App\Http\Middleware;

use Closure;
use Dcat\Admin\Admin;

class CheckUserLanguage
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
        // Get the currently logged-in user.
        $user = Admin::user();
        
        if ($user && $user->lang == 1) {
            app()->setLocale('en'); // Replace 'en' with your English locale.
        }

        return $next($request);
    }
}