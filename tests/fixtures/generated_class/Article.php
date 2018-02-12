<?php

namespace App;

class Article
{
    use \Darkilliant\ClassLogger\TraitLoggerProxy;
    private $id;

    /**
     * @return mixed
     */
    public function getId()
    {
        $body = function() {
            return $this->id;
        };

        $returnValue = $body();
        $this->_proxy_postCall('getId', [], $returnValue);

        return $returnValue;
    }

    /**
     * @param mixed $id
     */
    public function setId(integer $id)
    {
        $body = function(integer $id = null) {
            $this->id = $id;
        };

        $returnValue = $body($id);
        $this->_proxy_postCall('setId', [$id], $returnValue);

        return $returnValue;
    }
}