<?php

Route::group(['namespace' => 'Gezer\PagSeguro'], function () {
    Route::get('/pagseguro/session', 'PagSeguroController@session');
    Route::get('/pagseguro/javascript', 'PagSeguroController@javascript');
});
