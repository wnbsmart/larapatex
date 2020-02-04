<?php namespace Wnbsmart\Larapatex;

use Illuminate\Support\Facades\Facade;

class CpatexAPIFacade extends Facade {

    protected static function getFacadeAccessor() {
        return 'cpatex';
    }
}