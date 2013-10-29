<?php

namespace BoyHagemann\Wave;

/**
 * This class can read the chunks in the wave file and can analyze the
 * metadata and amplitudes.
 *
 * @author boyhagemann
 */
class Wave 
{
    /**
     * The path to the wave file
     *
     * @var string $filename
     */
    protected $filename;
    
    /**
     * This is the total size in bytes of the wave file
     *
     * @var integer $size
     */
    protected $size;

    /**
     * The chunks detected in the wavefile
     *
     * @var array $chunks
     */
    protected $chunks = array();

    /**
     * Use a filehandler to read the bytes in the wave file
     *
     * @var Stream $fileHandler
     */
    protected $fileHandler;
    
    /**
     * The number of steps to skip bytes.
     *
     * @var integer $steps;
     */
    protected $steps = 100;

    /**
     * Get the path to the wave file
     * 
     * @return string
     */
    public function getFilename() 
    {
        return $this->filename;
    }

    /**
     * Set the path to the wave file
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
     * Get the detected chunks in the wave file
     * 
     * @return array
     */
    public function getChunks() 
    {
        return $this->chunks;
    }

    /**
     * Set the chunk parts
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
     * Get the file handler that is used to read the bytes 
     * in the wave file
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
     * Set the file handler to read the wave file byte data
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
     * Get the number of steps for skipping when analyzing wave data
     * 
     * @return integer
     */
    public function getSteps() 
    {
        return $this->steps;
    }

    /**
     * Set the number of steps for skipping bytes when analyzing wave data
     * 
     * The higher the number, the more byte packages are skipped. This result
     * in a faster analyses, but makes the analyzed data less accurate.
     * 
     * The lower the number, the more detailed the analyses is. This can
     * exceed maximum execution time, so be careful not to set the number
     * of steps too small.
     * 
     * The default number of steps is 100
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
     * Get the total size of the wave file in number of bytes
     * 
     * @return integer
     */
    public function getSize() 
    {
        return $this->size;
    }

    /**
     * Read the wave file and detect the chunks. After this, the chunks are
     * ready to be analyzed to get the metadata en wave data
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

        // Get the total size of the wave file
        $this->size = current(unpack('V', fread($fh, 4)));

        // Check if the file is realy a wave file
        $format = fread($fh, 4);
        if($format !== 'WAVE') {
            throw new Exception(sprintf('Expected format "WAVE", but found type "%s"', $format));
        }

        $this->readChunks();

        return $this;
     }

    /**
     * Read a single chunk and get its name and size.
     * 
     * It creates a chunk object and adds it to the list of detected chunks.
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
       }        

       // Check if there is a chunk detected
       if($chunk) {
        $chunk->setSize($size);
        $chunk->setPosition($position);
        $this->setChunk($chunk);
       }
       
       // If the data chunk is found, then stop reading other (useless) chunks
       if(!$chunk instanceof Chunk\Data) {
           $this->readChunks();
       }
    }

    /**
     * Get the metadata from detected 'fmt' chunk. It contains information 
     * about:
     * - sample rate
     * - number of channels
     * - bits per sample
     * etc.
     * 
     * The metadata is set to a fixed order. The higher the size of the
     * metadata chunk, the more information is registered.
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
     * Get the amplitude data from the data chunk.
     * 
     * For speed optimalisation, skip some blocks. The number of skips is
     * based on the step size.
     * 
     * @uses Chunk\Data
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
        $skips              = $steps * $numberOfChannels * 2;

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
     * Create a channel object based on the number of channels of the
     * wave file. 
     * 
     * Returns an array containing the channel objects.
     * 
     * @param integer $numberOfChannels
     * @uses Channel
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
     * Get the amplitude of a single channel and a single data block
     * 
     * @param \BoyhagemannWave\Channel $channel
     */
    protected function readData(Channel $channel)
    {
       $fh = $this->getFileHandler();
	   $amplitude = current(unpack('V', fread($fh, 4)));
       $channel->setAmplitude(ftell($fh), $amplitude);
    }

    /**
     * Add a chunk object
     * 
     * @param Chunk\ChunkInterface $chunk
     */
    public function setChunk(Chunk\ChunkInterface $chunk)
    {
        $this->chunks[$chunk->getName()] = $chunk;
    }

    /**
     * Get the chunk object if it exists.
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
     * Get the wave data chunk object
     * 
     * This method triggers the analyzing of the chunk.
     * 
     * @return Chunk\Data
     */
    public function getWaveformData()
    {
        $this->analyzeData();
        return $this->getChunk(Chunk\Data::NAME);
    }
    
    /**
     * Get the metadata (fmt) chunk
     * 
     * This method triggers the analyzing of the chunk.
     * 
     * @return Chunk\Fmt
     */
    public function getMetadata()
    {
       $this->analyzeMetadata();
       return $this->getChunk(Chunk\Fmt::NAME);
    }
}