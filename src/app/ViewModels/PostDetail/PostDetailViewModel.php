<?php

namespace VCComponent\Laravel\Post\ViewModels\PostDetail;

use Carbon\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\ViewModel\ViewModels\BaseViewModel;

class PostDetailViewModel extends BaseViewModel
{
    public $post;

    public function __construct($post)
    {
        $this->post = $post;
    }

    public function getDisplayDatetimeAttribute()
    {

        return Carbon::parse($this->post->created_at)->format('d-m-Y h:i:s A');
    }

    public function getLimitDescription($limit = 30)
    {

        return Str::limit($this->post->description, $limit);
    }

    public function getLimitedName($limit = 10)
    {
        return Str::limit($this->post->name, $limit);
    }
}
