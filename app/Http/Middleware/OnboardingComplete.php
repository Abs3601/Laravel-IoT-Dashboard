<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;
use App\Models\User;
class OnboardingComplete
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $onboardingComplete = Setting::get('onboarding_completed');
        

        if ($request->is('/')) {
            if ($onboardingComplete) {
                return redirect()->route('home');
            }
            return $next($request);
        }
        
        if ($onboardingComplete) {
            return $next($request);
        }
        
        return redirect()->route('onboarding');
    }
}
