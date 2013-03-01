<?php

namespace BoyHagemann\Wave\Chunk;

/**
 * Description of Data
 *
 * @author boyhagemann
 */
class Fmt extends ChunkAbstract
{
    const  NAME = 'fmt ';
      
    protected $format;
    protected $channels;
    protected $sampleRate;
    protected $bytesPerSecond;
    protected $blockSize;
    protected $bitsPerSample;
    protected $extensionSize;
    protected $extensionData;
    
    /**
     * 
     * @see BoyhagemannWave\Chunk\ChunkInterface
     * @return string
     */
    public function getName()
    {
        return self::NAME;
    }
    

    public function getFormat() 
    {
        return $this->format;
    }

    public function setFormat($format) 
    {
        $this->format = $format;
        return $this;
    }

    public function getChannels() 
    {
        return $this->channels;
    }

    public function setChannels($channels) 
    {
        $this->channels = $channels;
        return $this;
    }

    public function getSampleRate()
    {
        return $this->sampleRate;
    }

    public function setSampleRate($sampleRate) 
    {
        $this->sampleRate = $sampleRate;
        return $this;
    }

    public function getBytesPerSecond() 
    {
        return $this->bytesPerSecond;
    }

    public function setBytesPerSecond($bytesPerSecond) 
    {
        $this->bytesPerSecond = $bytesPerSecond;
        return $this;
    }

    public function getBlockSize()
    {
        return $this->blockSize;
    }

    public function setBlockSize($blockSize) 
    {
        $this->blockSize = $blockSize;
        return $this;
    }

    public function getBitsPerSample() 
    {
        return $this->bitsPerSample;
    }

    public function setBitsPerSample($bitsPerSample) 
    {
        $this->bitsPerSample = $bitsPerSample;
        return $this;
    }

    public function getExtensionSize() 
    {
        return $this->extensionSize;
    }

    public function setExtensionSize($extensionSize) 
    {
        $this->extensionSize = $extensionSize;
        return $this;
    }

    public function getExtensionData() 
    {
        return $this->extensionData;
    }

    public function setExtensionData($extensionData) 
    {
        $this->extensionData = $extensionData;
        return $this;
    }
    
}