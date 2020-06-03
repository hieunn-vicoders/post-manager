<?php

namespace VCComponent\Laravel\Post\Traits;

trait PostManagementTrait
{
    public function ableToShow($user, $id)
    {
        return true;
    }

    public function ableToCreate($user)
    {
        return true;
    }

    public function ableToUpdate($user)
    {
        return true;
    }

    public function ableToUpdateItem($user, $id)
    {
        return true;
    }

    public function ableToDelete($user, $id)
    {
        return true;
    }
}
