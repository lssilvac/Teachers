<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



//Route::get('/finalizar-cadastro', TeacherProfile::class)->name('filament.painel.auth.profile');
