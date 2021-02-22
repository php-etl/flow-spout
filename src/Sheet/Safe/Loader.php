<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceIteratorResultBucket;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Component\Bucket\EmptyResultBucket;
use Kiboko\Contract\Bucket\ResultBucketInterface;
use Kiboko\Contract\Pipeline\FlushableInterface;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface, FlushableInterface
{
    public function __construct(
        private WriterInterface $writer,
        private \ArrayIterator $iterator
    ) {
    }

    public function load(): \Generator
    {
        $isFirstLine = true;
        $headers = [];

        foreach ($this->iterator as $d) {
            if (true === $isFirstLine) {
                $headers = array_keys($d);
                $this->writer->addRow(
                    new Row(array_map(fn ($value) => new Cell($value), array_keys($d)), null)
                );
                $isFirstLine = false;
            }

            $this->writer->addRow($this->orderColumns($headers, $d));

            yield new AcceptanceResultBucket($d);
        }
    }

    /*public function load(): \Generator
    {
        $line = yield;

        $isFirstLine = true;
        $headers = [];

        while (true) {
            if (true === $isFirstLine) {
                $headers = array_keys($line);
                $this->writer->addRow(
                    new Row(array_map(fn ($value) => new Cell($value), array_keys($line)), null)
                );
                $isFirstLine = false;
            }

            $this->writer->addRow($this->orderColumns($headers, $line));

            yield new AcceptanceResultBucket($line);
        }
    }*/

    private function orderColumns(array $headers, array $line): Row
    {
        $result = [];
        foreach ($headers as $cell) {
            $result[$cell] = new Cell($line[$cell] ?? null);
        }

        return new Row($result, null);
    }

    public function flush(): ResultBucketInterface
    {
        $this->writer->close();

        return new EmptyResultBucket();
    }
}
