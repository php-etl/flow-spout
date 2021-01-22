<?php

namespace Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Common\Entity\Cell;
use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\WriterInterface;
use Kiboko\Component\Bucket\AcceptanceResultBucket;
use Kiboko\Contract\Pipeline\LoaderInterface;

class Loader implements LoaderInterface
{
    public function __construct(
        private WriterInterface $writer
    ) {
    }

    public function load(): \Generator
    {
        $isFirstLine = true;
        $headers = [];
        while (true) {
            $line = yield;

            if ($isFirstLine === true) {
                $headers = array_keys($line);
                $this->writer->addRow($this->orderColumns($headers, $headers));
                $isFirstLine = false;
            }

            $this->writer->addRow($this->orderColumns($headers, $line));

            yield new AcceptanceResultBucket($line);
        }
    }

    private function orderColumns(array $headers, array $line): Row
    {
        $result = [];
        foreach ($headers as $cell) {
            $result[$cell] = new Cell($line[$cell] ?? null);
        }

        return new Row($result, null);
    }
}