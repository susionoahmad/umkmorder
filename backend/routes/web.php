<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => 'UMKMOrder Backend API',
        'version' => '1.0',
        'status' => 'online'
    ]);
});
