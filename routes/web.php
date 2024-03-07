<?php

use Illuminate\Support\Facades\Route;

Route::get('{path}', function () {
    return view('app');
})
->name('application')->where('path', '.*');
