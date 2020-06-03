<?php

namespace VCComponent\Laravel\Post\ViewModels\DrafDetail;

use Carbon\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\ViewModel\ViewModels\BaseViewModel;

class DrafDetailViewModel extends BaseViewModel
{
    public $draf;

    public function __construct($draf)
    {
        $this->draf = $draf;
    }

    public function getDisplayDatetimeAttribute()
    {

        return Carbon::parse($this->draf->created_at)->format('d-m-Y h:i:s A');
    }
}
