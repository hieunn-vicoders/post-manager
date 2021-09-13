<?php 

namespace VCComponent\Laravel\Post\Policies;

use VCComponent\Laravel\Post\Contracts\PostPolicyInterface;

class PostPolicy implements PostPolicyInterface
{
    public function ableToShow($user, $model)
    {
        return true;
    }

    public function ableToCreate($user)
    {
        return true;
    }

    public function ableToUpdateItem($user, $model)
    {
        return true;
    }

    public function ableToUpdate($user)
    {
        return true;
    }

    public function ableToDelete($user, $model)
    {
        return true;
    }
}