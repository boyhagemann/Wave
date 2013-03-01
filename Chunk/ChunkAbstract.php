<?php

namespace BoyHagemann\Wave\Chunk;

/**
 * Description of ChunkAbstract
 *
 * @author BoyHagemann
 */
abstract class ChunkAbstract implements ChunkInterface
{
    /**
     *
     * @var string $name
     */
    protected $name;
    
    /**
     *
     * @var integer $position
     */
    protected $position;
    
    /**
     *
     * @var integer $size
     */
    protected $size;
    
    /**
     * 
     * @param string $name
     * @return ChunkInterface
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
        return $this->name;
    }
    
    /**
     * 
     * @see BoyhagemannWave\Chunk\ChunkInterface
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }
    
    /**
     * 
     * @param integer $position
     * @return ChunkInterface
     */
    public function setPosition($position) 
    {
        $this->position = $position;
        return $this;
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
     * @return ChunkInterface
     */
    public function setSize($size) 
    {
        $this->size = $size;
        return $this;
    }
}

