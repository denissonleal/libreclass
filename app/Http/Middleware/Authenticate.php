<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
	/**
	 * Get the path the user should be redirected to when they are not authenticated.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return string
	 */
	protected function redirectTo($request)
	{
		if (auth()->guest()) {
			if ($request->ajax() || $request->wantsJson()) {
				return response('unauthenticated', 401);
			} else {
				return redirect('/login');
			}
		}

		return $next($request);
	}
}
