<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('login', 'API\AuthController@login')->name('login');
Route::post('registro', 'API\AuthController@registro')->name('registro');

Route::get('arquivos/{arquivo}', 'API\ArquivoController@show');

//CRUD PRODUTOS
Route::get('Produtos', 'API\ProdutoController@index');
Route::post('Produto', 'API\ProdutoController@show');

Route::middleware(['auth:api'])->group(function () {

    Route::get('meu-perfil', 'API\AuthController@meuPerfil')->name('perfil');

    Route::apiResource('arquivos', 'API\ArquivoController')->except([
        'show'
    ]);

    //CRUD COMPRAS
    Route::get('comprasIndex', 'API\CompraController@index');
    Route::post('comprasShow', 'API\CompraController@show');
    Route::post('minhasCompras', 'API\CompraController@userIndex');

    // PAGSEGURO
    Route::post('novaCompra', 'API\PagSeguroController@checkout');
    Route::get('Compras', 'API\PagSeguroController@transactions');

    Route::middleware(['admin'])->group(function () {
        //CRUD PRODUTOS
        Route::post('novoProduto', 'API\ProdutoController@store');
        Route::post('atualizaProduto', 'API\ProdutoController@update');
        Route::post('deletaProduto', 'API\ProdutoController@destroy');
 
    });
});

Route::get('teste', function () {
    return 'abc';
});
