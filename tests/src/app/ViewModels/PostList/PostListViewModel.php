<?php

namespace VCComponent\Laravel\Post\ViewModels\PostList;

use Carbon\Carbon;
use Illuminate\Support\Str;
use VCComponent\Laravel\ViewModel\ViewModels\BaseViewModel;

class PostListViewModel extends BaseViewModel
{
    public $posts;

    public function __construct($posts)
    {
        $this->posts = $posts;
    }

    public function getDisplayDatetimeAttribute($item)
    {
        return Carbon::parse($item->created_at)->format('d-m-Y h:i:s A');
    }

    public function getLimitDescription($item, $limit = 30)
    {

        return Str::limit($item->description, $limit);
    }

    public function getLimitedName($item, $limit = 10)
    {
        return Str::limit($item->title, $limit);
    }
}
