<?php

namespace VCComponent\Laravel\Post\ViewModels\DraftDetail;

use Carbon\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\ViewModel\ViewModels\BaseViewModel;

class DraftDetailViewModel extends BaseViewModel
{
    public $draft;

    public function __construct($draft)
    {
        $this->draft = $draft;
    }

    public function getDisplayDatetimeAttribute()
    {

        return Carbon::parse($this->draft->created_at)->format('d-m-Y h:i:s A');
    }
}
