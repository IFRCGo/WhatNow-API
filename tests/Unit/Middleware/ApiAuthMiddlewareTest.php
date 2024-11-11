<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class ApiAuthMiddlewareTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp(): void
    {
        parent::setUp();

        DB::delete('delete from applications');
    }

    public function test_unauthorised_call_returns_401()
    {
        // Create request
        $url = $this->prepareUrlForRequest('/');
        $request = Request::create($url, 'GET');

        // Pass it to the middleware
        $middleware = new \App\Http\Middleware\ApiAuthMiddleware();

        try {
            $middleware->handle($request, function () {
            });
            $statusCode = 200;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
        }

        $this->assertSame(401, $statusCode);
    }

    public function test_authorised_with_app_key_passes()
    {
        \App\Models\Application::create([
            'tenant_user_id' => '999',
            'tenant_id' => 1,
            'name' => 'test',
            'description' => null,
            'key' => '1234567890',
        ]);

        // Create request
        $url = $this->prepareUrlForRequest('/');
        $request = Request::create($url, 'GET');
        $request->headers->set('x-api-key', '1234567890');

        // Pass it to the middleware
        $middleware = new \App\Http\Middleware\ApiAuthMiddleware();

        try {
            $middleware->handle($request, function () {
            });
            $statusCode = 200;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
        }

        $this->assertSame(200, $statusCode);
    }

    public function test_authorised_with_basic_auth_passes()
    {
        // Create request
        $url = $this->prepareUrlForRequest('/');
        $request = Request::create($url, 'GET');
        $request->headers->set('PHP_AUTH_USER', getenv('ADMIN_USER'));
        $request->headers->set('PHP_AUTH_PW', getenv('ADMIN_PASSWORD'));

        // Pass it to the middleware
        $middleware = new \App\Http\Middleware\ApiAuthMiddleware();

        try {
            $middleware->handle($request, function () {
            });
            $statusCode = 200;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
        }

        $this->assertSame(200, $statusCode);
    }

    public function test_only_basic_auth_route_fails_with_api_key()
    {
        \App\Models\Application::create([
            'tenant_user_id' => '999',
            'tenant_id' => 1,
            'name' => 'test',
            'description' => null,
            'key' => '1234567890',
        ]);

        // Create request
        $url = $this->prepareUrlForRequest('/');
        $request = Request::create($url, 'GET');

        $request->headers->set('x-api-key', '1234567890');

        // Pass it to the middleware
        $middleware = new \App\Http\Middleware\BasicAuthMiddleware();

        try {
            $middleware->handle($request, function () {
            });
            $statusCode = 200;
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $statusCode = $e->getStatusCode();
        }

        $this->assertSame(401, $statusCode);
    }
}
