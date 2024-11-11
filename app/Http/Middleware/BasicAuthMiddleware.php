<?php

namespace App\Http\Middleware;

use Closure;

class BasicAuthMiddleware
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
        $this->basicAuth($request);

        return $next($request);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function basicAuth($request)
    {
        if (is_null($request->getUser()) || is_null($request->getPassword())) {
            $headers = ['WWW-Authenticate' => 'Basic'];
            abort(401, 'Unauthorized', $headers);
        }

        if ($request->getUser() !== config('app.admin_user') || ! hash_equals($request->getPassword(), config('app.admin_password'))) {
            $headers = ['WWW-Authenticate' => 'Basic'];

            abort(401, 'Unauthorized', $headers);
        }
    }
}
