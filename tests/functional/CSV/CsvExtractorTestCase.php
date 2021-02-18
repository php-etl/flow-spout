<?php

declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\CSV;

use Box\Spout\Common\Creator\HelperFactory;
use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Reader\CSV\Creator\InternalEntityFactory;
use Box\Spout\Reader\CSV\Manager\OptionsManager;
use Box\Spout\Reader\CSV\Reader;
use Kiboko\Component\Flow\Spreadsheet\CSV\Safe\Extractor;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

class CsvExtractorTestCase extends TestCase
{
    private ?FileSystem $fs = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;
    }

    public function testExtractCsv(): void
    {
        $reader = new Reader(
            new OptionsManager(),
            new GlobalFunctionsHelper(),
            new InternalEntityFactory(
                new HelperFactory()
            )
        );

        $reader->open('tests/functional/CSV/source-to-extract.csv');

        $extractor = new Extractor($reader, 0);

        $result = [];
        foreach ($extractor->extract() as $line) {
            $result[] = $line;
        }

        $this->assertEquals(
            [
                [
                    'first name' => 'john',
                    'last name' => 'doe',
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont',
                ],
            ],
            $result
        );
    }
}
