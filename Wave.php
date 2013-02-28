<?php

namespace BoyHagemann\Wave;

/**
 * Description of Wave
 *
 * @author boyhagemann
 */
class Wave 
{
    /**
     *
     * @var string $filename
     */
    protected $filename;
    
    /**
     *
     * @var integer $length
     */
    protected $length;

    /**
     *
     * @var array $chunks
     */
    protected $chunks = array();

    /**
     *
     * @var Stream $fileHandler
     */
    protected $fileHandler;
    
    /**
     *
     * @var integer $steps;
     */
    protected $steps = 100;
    
    /**
     *
     * @var integer $position
     */
    private $position = 0;

    /**
     * 
     * @return string
     */
    public function getFilename() 
    {
        return $this->filename;
    }

    /**
     * 
     * @param string $filename
     * @return Wave
     */
    public function setFilename($filename) 
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getChunks() 
    {
        $this->analyze();
        return $this->chunks;
    }

    /**
     * 
     * @param array $chunks
     * @return \BoyhagemannWave\Wave
     */
    public function setChunks(Array $chunks) 
    {
        $this->chunks = $chunks;
        return $this;
    }

    /**
     * 
     * @return Stream
     */
    public function getFileHandler() 
    {
        if(!$this->fileHandler) {
            $this->fileHandler = fopen($this->getFilename(), 'r');
        }

        return $this->fileHandler;
    }

    /**
     * 
     * @param \BoyhagemannWave\Stream $fileHandler
     * @return \BoyhagemannWave\Wave
     */
    public function setFileHandler(Stream $fileHandler) 
    {
        $this->fileHandler = $fileHandler;
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getSteps() 
    {
        return $this->steps;
    }

    /**
     * 
     * @param integer $steps
     * @return \BoyhagemannWave\Wave
     */
    public function setSteps($steps) 
    {
        $this->steps = $steps;
        return $this;
    }

    /**
     * 
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * 
     * @param integer $increment
     */
    protected function incrementPosition($increment)
    {
        $this->position += $increment;
    }

    /**
     * 
     * @return Chunk\Data
     */
    public function getWaveformData()
    {
        $this->analyze();
        return $this->getChunk(Chunk\Data::NAME);
    }

    /**
     * 
     * @return Waveform
     */
    public function getWaveform()
    {
        return new Waveform($this);
    }

    /**
     * 
     * @return integer
     */
    public function getLength() 
    {
        $this->analyze();
        return $this->length;
    }

    /**
     * 
     * @throws Exception
     * @return Wave
     */
    public function analyze()
    {
        $fh = $this->getFileHandler();

        // Check if the file is a RIFF file
        $type = fread($fh, 4);
        if($type !== 'RIFF') {
            throw new Exception(sprintf('Expected type RIFF, but found type "%s"', $type));
        }
        
        $this->length = current(unpack('V', fread($fh, 4)));
                
        // Check if the file is realy a wave file
        $format = fread($fh, 4);
        if($format !== 'WAVE') {
            throw new Exception(sprintf('Expected format "WAVE", but found type "%s"', $format));
        }
        
        $this->incrementPosition(12);
        $this->readChunkAtCurrentPointer();
        
        return $this;
     }
     
     /**
      * 
      */
     protected function readChunkAtCurrentPointer()
     {
        $fh = $this->getFileHandler();         
        
        $name = fread($fh, 4);
        $size =  current(unpack('V', fread($fh, 4)));  
        $this->incrementPosition(8);
        
        switch(strtolower($name)) {
            
            case Chunk\Fmt::NAME:
                
                $chunk = new Chunk\Fmt();
                
                if ($size >= 2) {
                    $format = current(unpack('v', fread($fh, 2)));
                    $chunk->setFormat($format);
                    $this->incrementPosition(2);
                }                
                if ($size >= 4) {
                    $channels = current(unpack('v', fread($fh, 2)));
                    $chunk->setChannels($channels);
                    $this->incrementPosition(2);
                }                
                if ($size >= 8) {
                    $sampleRate = current(unpack('V', fread($fh, 4)));
                    $chunk->setSampleRate($sampleRate);
                    $this->incrementPosition(4);
                }
                if ($size >= 12) {
                    $bytesPerSecond = current(unpack('V', fread($fh, 4)));
                    $chunk->setBytesPerSecond($bytesPerSecond);
                    $this->incrementPosition(4);
                }
                if ($size >= 14) {
                    $blockSize = current(unpack('v', fread($fh, 2)));
                    $chunk->setBlockSize($blockSize);
                    $this->incrementPosition(2);
                }
                if ($size >= 16) {
                    $bitsPerSample = current(unpack('v', fread($fh, 2)));
                    $chunk->setBitsPerSample($bitsPerSample);
                    $this->incrementPosition(2);
                }
                if ($size >= 18) {
                    $extensionSize = current(unpack('v', fread($fh, 2)));
                    $chunk->setExtensionSize($extensionSize);
                    $this->incrementPosition(2);
                }
                if ($size >= 20) {
                    $extensionData = fread($fh, $extensionSize);
                    $chunk->setExtensionData($extensionData);
                    $this->incrementPosition($extensionSize);
                }
                
                $this->setChunk($chunk);
                $this->readChunkAtCurrentPointer();                
                break;
            
            case 'data':
                
                $numberOfChannels   = $this->getMetadata()->getChannels();
                $channels           = $this->createChannels($numberOfChannels);                
                $chunk              = new Chunk\Data($size);
                $steps              = $this->getSteps();
                $blockSize          = $this->getMetadata()->getBlockSize();       
                $skips              = $steps * $blockSize;
                
                while(!feof($fh)) {
                    
                    foreach($channels as $channel) {
                        $this->readData($channel);
                    }    
                    
                    fread($fh, $skips);
                    $this->incrementPosition(2 + $skips);
                }
                
                $chunk->setChannels($channels);
                $this->setChunk($chunk);
                break;
                                
            default:
                $chunk = new Chunk\Other($name, $size);
                $data = fread($fh, $size);
                $chunk->setData($data);
                $this->readChunkAtCurrentPointer();
        }
     }
     
     /**
      * 
      * @param integer $numberOfChannels
      * @return array
      */
     public function createChannels($numberOfChannels)
     {
         $channels = array();
         for($i = 0; $i < $numberOfChannels; $i++) {
             $channels[] = new Channel();
         }
         return $channels;
     }
     
     /**
      * 
      * @param \BoyhagemannWave\Channel $channel
      */
     protected function readData(Channel $channel)
     {
        $fh         = $this->getFileHandler();
        $position   = $this->getPosition();
        $amplitude  = current(unpack('v', fread($fh, 2)));
        
        $channel->setAmplitude($position, $amplitude);
     }

     /**
      * 
      * @param Chunk\ChunkInterface $chunk
      */
     public function setChunk(Chunk\ChunkInterface $chunk)
     {
         $this->chunks[$chunk->getName()] = $chunk;
     }

     /**
      * 
      * @throws Exception
      * @return Chunk\ChunkInterface
      */
     public function getChunk($name)
     {
         if(!key_exists($name, $this->chunks)) {
             throw new Exception(sprintf('No chunk with name "%s" set', $name));
         }

         return $this->chunks[$name];
     }
     
     /**
      * 
      * @return Chunk\Fmt
      */
     public function getMetadata()
     {
         return $this->getChunk(Chunk\Fmt::NAME);
     }
}