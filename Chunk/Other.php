<?php

namespace BoyHagemann\Wave\Chunk;

/**
 * Description of Data
 *
 * @author boyhagemann
 */
class Other extends ChunkAbstract
{
    protected $data;
    
    /**
     * 
     * @param string $name
     * @param integer $size
     */
    public function __construct($name = null, $size = null) 
    {
        if($name) {
            $this->setName($name);
        }
        
        if($size) {
            $this->setSize($size);
        }
    }

    /**
     * 
     * @return string
     */
    public function getData() 
    {
        return $this->data;
    }

    /**
     * 
     * @param string $data
     * @return \BoyhagemannWave\Chunk\Other
     */
    public function setData($data) 
    {
        $this->data = $data;
        return $this;
    }



}