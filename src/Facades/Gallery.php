<?php

namespace Parvez\GalleryManager\Facades;

use Illuminate\Support\Facades\Facade;

class Gallery extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'gallery';
    }
}
