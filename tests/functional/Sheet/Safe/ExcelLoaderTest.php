<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\Safe;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX;
use functional\Kiboko\Component\Flow\Spreadsheet\ExcelAssertTrait;
use Kiboko\Component\PHPUnitExtension\PipelineAssertTrait;
use Kiboko\Component\Flow\Spreadsheet\Sheet\Safe\Loader;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class ExcelLoaderTest extends TestCase
{
    use PipelineAssertTrait;
    use ExcelAssertTrait;

    private ?FileSystem $fs = null;
    private ?XLSX\Writer $writer = null;

    protected function setUp(): void
    {
        $this->fs = FileSystem::factory('vfs://');
        $this->fs->mount();

        $this->writer = WriterEntityFactory::createXLSXWriter();
    }

    protected function tearDown(): void
    {
        $this->fs->unmount();
        $this->fs = null;

        $this->writer = null;
    }

    public function testLoad()
    {
        $path = tempnam(sys_get_temp_dir(), 'spreadsheet_');

        $this->writer->openToFile(/*'vfs://test.xlsx'*/$path);

        $this->assertPipelineDoesLoadLike(
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
            new Loader($this->writer, 'Sheet1')
        );

        $this->assertRowWasWrittenToExcel(
            /*'vfs://test.xlsx'*/$path,
            'Sheet1',
            ['first name', 'last name'],
        );

        $this->assertRowWasWrittenToExcel(
            /*'vfs://test.xlsx'*/$path,
            'Sheet1',
            ['john', 'doe'],
        );

        $this->assertRowWasWrittenToExcel(
            /*'vfs://test.xlsx'*/$path,
            'Sheet1',
            ['jean', 'dupont'],
        );
    }
}
