<?php

use App\Livewire\Admin\Dashboard\Index as DashboardIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardIndex::class)->name('dashboard');
