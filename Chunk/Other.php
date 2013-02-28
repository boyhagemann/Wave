<?php

namespace BoyhagemannWave\Chunk;

/**
 * Description of Data
 *
 * @author boyhagemann
 */
class Other implements ChunkInterface
{
    protected $name;
    protected $size;
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
     * @param string $name
     * @return \BoyhagemannWave\Chunk\Other
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }


    /**
     * 
     * @see BoyhagemannWave\Chunk\ChunkInterface
     * @return string
     */
    public function getName()
    {
        return $this->data;
    }
    
    /**
     * 
     * @see BoyhagemannWave\Chunk\ChunkInterface
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }
    
    /**
     * 
     * @param integer $size
     * @return \BoyhagemannWave\Chunk\Other
     */
    public function setSize($size) 
    {
        $this->size = $size;
        return $this;
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