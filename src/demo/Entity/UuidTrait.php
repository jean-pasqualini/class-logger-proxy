<?php

namespace App\Entity;

trait UuidTrait
{
    public function getUuid()
    {
        return md5(uniqid());
    }
}