<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResumeController;
use App\Http\Controllers\MatchController;

Route::post('/resume', [ResumeController::class, 'upload']);
Route::post('/match', [MatchController::class, 'match']);
