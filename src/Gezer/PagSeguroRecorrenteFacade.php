<?php

namespace Gezer\PagSeguro;

use Illuminate\Support\Facades\Facade;

class PagSeguroRecorrenteFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pagseguro_recorrente';
    }
}
