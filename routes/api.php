<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1'], function () {
    // Unauthenticated endpoints
    Route::get('alerts/rss', 'AlertController@getRss');
    Route::get('alerts/{identifier}', 'AlertController@getByIdentifier');
    Route::get('alerts', 'AlertController@get');
    Route::get('org/{code}/alerts', 'AlertController@getByOrg');
    Route::get('org/{code}/alerts/rss', 'AlertController@getRssByOrg');
});

Route::group(['middleware' => 'BasicAuth', 'prefix' => 'v1'], function () {
    // Alert management
    Route::post('alerts', 'AlertController@post');
});

Route::group([
    'middleware' => 'ApiAuth',
    'prefix' => 'v1',
], function () {
    // Endpoints requiring API key authentication
    Route::get('org/', 'OrganisationController@getAll');
    Route::get('org/{code}', 'OrganisationController@getById');
    Route::get('org/{code}/whatnow', 'WhatNowController@getFeed');
    Route::get('whatnow/{id}', 'WhatNowController@getPublishedById');
});

Route::group([
    'middleware' => 'BasicAuth',
    'prefix' => 'v1',
], function () {
    Route::get('/regions/{country_code}', 'RegionController@getAllForOrganisation');
    Route::get('/regions/{country_code}/{code}', 'RegionController@getForCountryCode');
    Route::post('/regions', 'RegionController@createRegion');
    Route::put('/regions/region/{regionId}', 'RegionController@updateRegion');
    Route::delete('/regions/region/{regionId}', 'RegionController@deleteRegion');
    Route::delete('/regions/region/translation/{translationId}', 'RegionController@deleteTranslation');

    // Alert management
    Route::post('alerts', 'AlertController@post');

    // Organisation management
    Route::put('org/{code}', 'OrganisationController@putById');
    // Route::post('org/{code}/image', 'OrganisationController@postImageById');
    // Route::delete('org/{code}/image', 'OrganisationController@deleteImageById');

    // "Applications" endpoints
    Route::get('apps', 'ApplicationController@getAllForUser');
    Route::post('apps', 'ApplicationController@create');
    Route::get('apps/{id}', 'ApplicationController@getById');
    Route::delete('apps/{id}', 'ApplicationController@delete');
    Route::patch('apps/{id}', 'ApplicationController@update');

    // Usage log endpoints
    Route::get('usage/applications', 'UsageLogController@getApplicationLogs');
    Route::get('usage/endpoints', 'UsageLogController@getEndpointLogs');
    Route::get('usage/export', 'UsageLogController@export');
    Route::get('usage/totals', 'UsageLogController@getTotals');

    // "What Now" API endpoints
    Route::get('org/{code}/whatnow/revisions/latest', 'WhatNowController@getLatestForCountryCode');
    Route::get('org/{code}/{region}/whatnow/revisions/latest', 'WhatNowController@getLatestForRegion');
    Route::get('whatnow/{id}/revisions/latest', 'WhatNowController@getLatestById');
    Route::put('whatnow/{id}', 'WhatNowController@putById');
    Route::post('whatnow', 'WhatNowController@post');
    Route::post('whatnow/{id}/revisions', 'WhatNowController@createNewTranslation');
    Route::post('whatnow/publish', 'WhatNowController@publishTranslationsByIds');
    Route::patch('whatnow/{id}/revisions/{translationId}',  'WhatNowController@patchTranslation');
    Route::delete('whatnow/{id}', 'WhatNowController@deleteById');

    // File upload
    Route::post('upload', 'FileUploadController@upload');
});

Route::get('/health', function () {
    return response()->json(['status' => 'ok']); // Or a more detailed status
});
