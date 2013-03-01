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
        
        // Read the file, get the chunks
        $this->read();
        
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getChunks() 
    {
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
        return $this->length;
    }

    /**
     * 
     * @throws Exception
     * @return Wave
     */
    protected function read()
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

        $this->readChunks();

        return $this;
     }

    /**
     * 
     */
    protected function readChunks()
    {         
       $fh = $this->getFileHandler();        

       $name = fread($fh, 4);
       $size = current(unpack('V', fread($fh, 4))); 
       $position = ftell($fh);

       fseek($fh, $position + $size);

       switch(strtolower($name)) {

           case Chunk\Fmt::NAME:
               $chunk = new Chunk\Fmt;
               break;

           case Chunk\Data::NAME:
               $chunk = new Chunk\Data;
               break;

           default:
               $chunk = new Chunk\Other();
               $chunk->setName($name);
               $chunk->setData(fread($fh, $size));
       }        

       $chunk->setSize($size);
       $chunk->setPosition($position);
       $this->setChunk($chunk);

       if(!$chunk instanceof Chunk\Data) {
           $this->readChunks();
       }
    }

    /**
     * 
     */
    protected function analyzeMetadata()
    {
        $chunk      = $this->getChunk(Chunk\Fmt::NAME);
        $size       = $chunk->getSize();
        $position   = $chunk->getPosition();
        $fh         = $this->getFileHandler();

        fseek($fh, $position);                

        if ($size >= 2) {
            $format = current(unpack('v', fread($fh, 2)));
            $chunk->setFormat($format);
        }                
        if ($size >= 4) {
            $channels = current(unpack('v', fread($fh, 2)));
            $chunk->setChannels($channels);
        }                
        if ($size >= 8) {
            $sampleRate = current(unpack('V', fread($fh, 4)));
            $chunk->setSampleRate($sampleRate);
        }
        if ($size >= 12) {
            $bytesPerSecond = current(unpack('V', fread($fh, 4)));
            $chunk->setBytesPerSecond($bytesPerSecond);
        }
        if ($size >= 14) {
            $blockSize = current(unpack('v', fread($fh, 2)));
            $chunk->setBlockSize($blockSize);
        }
        if ($size >= 16) {
            $bitsPerSample = current(unpack('v', fread($fh, 2)));
            $chunk->setBitsPerSample($bitsPerSample);
        }
        if ($size >= 18) {
            $extensionSize = current(unpack('v', fread($fh, 2)));
            $chunk->setExtensionSize($extensionSize);
        }
        if ($size >= 20) {
            $extensionData = fread($fh, $extensionSize);
            $chunk->setExtensionData($extensionData);
        }

    }

    /**
     * 
     */
    protected function analyzeData()
    {
        $chunk              = $this->getChunk(Chunk\Data::NAME);
        $position           = $chunk->getPosition();
        $size               = $chunk->getSize();
        $numberOfChannels   = $this->getMetadata()->getChannels();
        $channels           = $this->createChannels($numberOfChannels);                
        $steps              = $this->getSteps();
        $blockSize          = $this->getMetadata()->getBlockSize();       
        $skips              = $steps * $blockSize;

        $fh = $this->getFileHandler();
        fseek($fh, $position);

        while(!feof($fh) && ftell($fh) < $position + $size) {

            foreach($channels as $channel) {
                $this->readData($channel);
            }    

            fseek($fh, $skips, SEEK_CUR);
        }

        $chunk->setChannels($channels);
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
       $fh = $this->getFileHandler();      
       $amplitude = current(unpack('v', fread($fh, 2)));       
       $channel->setAmplitude(ftell($fh), $amplitude);
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
     * @return Chunk\Data
     */
    public function getWaveformData()
    {
        $this->analyzeData();
        return $this->getChunk(Chunk\Data::NAME);
    }
    
    /**
     * 
     * @return Chunk\Fmt
     */
    public function getMetadata()
    {
       $this->analyzeMetadata();
       return $this->getChunk(Chunk\Fmt::NAME);
    }
}