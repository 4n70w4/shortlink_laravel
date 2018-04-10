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

Route::get('/', function () {
    return view('home');
});

Route::post('/', function(\App\Http\Controllers\HomeControler $controller, \App\Http\Requests\AddLinkRequest $request) {
    return $controller->addAction($request);
});



Route::get('/{hash}', function(\App\Http\Controllers\HomeControler $controller, $hash) {
    return $controller->redirectAction($hash);
})->where(['hash' => '[a-zA-Z0-9]+'])->name('redirect');