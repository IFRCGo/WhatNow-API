<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->to('https://whatnow.preparecenter.org');
});
// API documentation
Route::get('/endpoints', [\App\Http\Controllers\RouteController::class, 'index'])
    ->name('endpoints.index');  // Displays list of available API endpoints with documentation
