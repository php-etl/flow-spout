<?php

declare(strict_types=1);

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;

final class Loader implements LoaderInterface, FlushableInterface
{
    public function __construct(
        private WriterInterface $writer
    ) {
    }

    public function load(): \Generator
    {
        $line = yield;
        $this->writer->addRow(
            new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
        );

        while (true) {
            $this->writer->addRow(
                new Row(array_map(fn ($value) => new Cell($value), $line), null)
            );

            $line = yield new AcceptanceResultBucket($line);
        }
    }

    public function flush(): ResultBucketInterface
    {
        $this->writer->close();

        return new EmptyResultBucket();
    }
}
