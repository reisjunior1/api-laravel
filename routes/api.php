<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/criar_conta', 'contaApiController@criar_conta')->name('criar_conta');
Route::post('/deposito', 'contaApiController@deposito')->name('deposito');
Route::post('/sacar', 'contaApiController@sacar')->name('sacar');
Route::post('/ver_saldo', 'contaApiController@ver_saldo')->name('ver_saldo');


