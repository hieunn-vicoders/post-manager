<?php 

namespace VCComponent\Laravel\Post\Policies;

use VCComponent\Laravel\Post\Contracts\PostPolicyInterface;

class PostPolicy implements PostPolicyInterface
{
    public function before($user, $ability)
    {
        if ($user->isAdministrator()) {
            return true;
        }
    }

    public function view($user, $model)
    {
        return $user->hasPermission('view-post');
    }

    public function create($user)
    {
        return $user->hasPermission('create-post');
    }

    public function updateItem($user, $model)
    {
        return $user->hasPermission('update-item-post');
    }

    public function update($user)
    {
        return $user->hasPermission('update-post');
    }

    public function delete($user, $model)
    {
        return $user->hasPermission('delete-post');
    }
}