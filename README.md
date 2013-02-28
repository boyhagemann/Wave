Wave
====

This package can view metadata from a wave file. It also reads the data chunks and seperates it into multiple channels.
Each channel has its own amplitude values. This is useful if you want to render a [waveform](http://github.com/boyhagemann/Waveform) for instance.

## How to install

You can install with composer. Use a composer.json file with the following lines:

```
"minimum-stability": "dev",
"require": {
    "boyhagemann/wave": "dev-master"
}
```

## How to use

First, let's make a wave object based on a wave file:
```
<?php

use BoyHagemann\Wave\Wave;

$wave = new Wave();
$wave->setFilename('path/to/your/file.wav');
```

After that, if the file turns out to be a valid wave file, you can
get several metadata from it.

## Chunks

A wave file is made out of chunks; packages of data. Each chunk has its own name, size and unique data.
For instance, the "Fmt" chunk contains a description of the wave file contents. To get this metadata,
you can enter these lines:
```
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
