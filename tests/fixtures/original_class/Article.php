<?php

namespace App;

class Article
{
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId(integer $id)
    {
        $this->id = $id;
    }
}