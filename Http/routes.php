<?php

Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\LINEIntegration\Http\Controllers'], function()
{
    Route::get('/', 'LINEIntegrationController@index');
});

Route::group([/*'middleware' => 'web', */'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\LINEIntegration\Http\Controllers'], function()
    {
        Route::match(['get', 'post'], '/line/webhook/{mailbox_id}', 'LINEIntegrationController@webhooks')->name('line.webhook');
    });

// Admin.
Route::group(['middleware' => 'web', 'prefix' => \Helper::getSubdirectory(), 'namespace' => 'Modules\LINEIntegration\Http\Controllers'], function()
{
    Route::get('/mailbox/{mailbox_id}/line', ['uses' => 'LINEIntegrationController@settings', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']])->name('mailboxes.line.settings');
    Route::post('/mailbox/{mailbox_id}/line', ['uses' => 'LINEIntegrationController@settingsSave', 'middleware' => ['auth', 'roles'], 'roles' => ['admin']]);
});