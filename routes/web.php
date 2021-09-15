<?php

use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return view('welcome');
});

Route::get('obter_moedas', 'contaApiController@obter_moedas');
Route::get('cotacao', 'contaApiController@cotacao');
Route::get('insere_brl', 'contaApiController@insere_brl');
//Route::get('contas', 'contaApiController@conta')->name('conta');