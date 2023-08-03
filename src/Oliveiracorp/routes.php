<?php

Route::group(['namespace' => 'Oliveiracorp\PagSeguro'], function () {
    Route::get('/pagseguro/session', 'PagSeguroController@session');
    Route::get('/pagseguro/javascript', 'PagSeguroController@javascript');
});
