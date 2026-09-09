<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Serves the Single Page Application which communicates with the REST APIs
| entirely via JavaScript fetch().
|
*/

Route::get('/', function () {
    return view('billing');
})->name('billing');

Route::get('/billing', function () {
    return redirect()->route('billing');
});
