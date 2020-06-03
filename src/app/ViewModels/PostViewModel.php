<?php

namespace VCComponent\Laravel\Post\ViewModels;

use Carbon\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\ViewModel\ViewModels\BaseViewModel;

class PostViewModel extends BaseViewModel
{
    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }
}
