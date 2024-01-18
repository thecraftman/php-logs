<?php

use App\Http\Controllers\TestController;

Route::get('/test-log', [TestController::class, 'logTest']);
