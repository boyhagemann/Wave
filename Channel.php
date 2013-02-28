<?php

namespace BoyHagemann\Wave;

/**
 * Description of Channel
 *
 * @author boyhagemann
 */
class Channel 
{
    /**
     *
     * @var string $name
     */
    protected $name;
    
    /**
     *
     * @var array 
     */
    protected $values = array();
    
    /**
     * 
     * @return string
     */
    public function getName() 
    {
        return $this->name;
    }

    /**
     * 
     * @param string $name
     * @return Channel
     */
    public function setName($name) 
    {
        $this->name = $name;
        return $this;
    }
    
    /**
     * 
     * @param integer $position
     * @param integer $value
     */
    public function setAmplitude($position, $amplitude)
    {
        $this->values[$position] = $amplitude;
    }
    
    /**
     * 
     * @return array
     */
    public function getValues()
    {
        return $this->values;
    }

}