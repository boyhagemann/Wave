<?php

namespace BoyHagemann\Wave\Chunk;

/**
 *
 * @author boyhagemann
 */
interface ChunkInterface 
{
    /**
     * 
     * @return string
     */
    public function getName();
    
    /**
     * 
     * @return integer
     */
    public function getPosition();
    
    /**
     * 
     * @parram integer $position
     */
    public function setPosition($position);

    /**
     * 
     * @return integer
     */
    public function getSize();

    /**
     * 
     * @param integer $size
     */
    public function setSize($size);
}