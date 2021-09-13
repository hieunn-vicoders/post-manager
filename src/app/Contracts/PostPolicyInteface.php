<?php 

namespace VCComponent\Laravel\Post\Contracts;

interface PostPolicyInterface 
{
    public function ableToShow($user, $model);
    public function ableToCreate($user);
    public function ableToUpdate($user);
    public function ableToUpdateItem($user, $model);
    public function ableToDelete($user, $model);
}