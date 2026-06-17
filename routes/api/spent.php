<?php

use App\Http\Controllers\TimeSpent\TimeSpentController;
Route::post('/', [TimeSpentController::class, 'update']);
