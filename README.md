# Wave

This package can view metadata from a wave file. It also reads the data chunks and seperates it into multiple channels.
Each channel has its own amplitude values. This is useful if you want to render a [waveform](http://github.com/boyhagemann/Waveform) for instance.

## How to install

You can install with composer. Use a composer.json file with the following lines:

```json
"minimum-stability": "dev",
"require": {
    "boyhagemann/wave": "dev-master"
}
```

## How to use

First, let's make a wave object based on a wave file:
```php
<?php

use BoyHagemann\Wave\Wave;

$wave = new Wave();
$wave->setFilename('path/to/your/file.wav');
```

After that, if the file turns out to be a valid wave file, you can
get several metadata from it.

## How a wave is made

A wave file is made out of chunks; packages of data. 
Each chunk has its own name, size and unique data.
We are actually only interested in 2 types of chunk: the Fmt and the Data chunk.

### Metadata (fmt chunk)
For instance, the "Fmt" chunk contains a description of the wave file contents. To get this metadata,
you can enter these lines:
```php
$metadata = $wave->analyze()->getMetadata();
$metadata->getName();
$metadata->getSize();
$metadata->getFormat();
$metadata->getChannels();
$metadata->getSampleRate();
$metadata->getBytesPerSecond();
$metadata->getBlockSize();
$metadata->getBitsPerSample();
$metadata->getExtensionSize();
$metadata->getExtensionData();
```

### Data chunk

This chunk contains all the actual wave data. It is build up in packages of several bytes, depending on the
number of channels the wave has. All analyzed data is stored in seperate channels, depending on the number of
channels of the file. To get the raw amplitudes of a file, do the following:
```php
// Assuming we already analyzed the wave...
$data = $wave->getWaveformData();

// Get the amplitude values for each channel
foreach($data->getChannels() as $channel) {
    $amplitudes[] = $channel->getValues();
}
```

## Analyzing

The analyzing process can be a hefty one. Normally, you scan every package in the data chunk. 
It can use all your php processing power and can quickly result in a maximum execution time error. 
To prevent this, you can set the level of detail for analysing the data.
You can set the number of steps between the packages that are to be analyzed.
The greater the steps, the faster the script will run. 
The smaller the steps, the more accurate the waveform will be.
By default, the steps are set to 100, but you can alter this easily:
```php
$wave->setSteps(10000);
```
