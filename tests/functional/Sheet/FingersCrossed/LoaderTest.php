<?php declare(strict_types=1);

namespace functional\Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed;

use Box\Spout\Common\Helper\GlobalFunctionsHelper;
use Box\Spout\Writer\Common\Creator\InternalEntityFactory;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\XLSX\Creator\HelperFactory;
use Box\Spout\Writer\XLSX\Creator\ManagerFactory;
use Box\Spout\Writer\XLSX\Manager\OptionsManager;
use Box\Spout\Writer\XLSX\Writer;
use Kiboko\Component\Flow\Spreadsheet\Sheet\FingersCrossed\Loader;
use Kiboko\Component\Pipeline\Pipeline;
use Kiboko\Component\Pipeline\PipelineRunner;
use PHPUnit\Framework\TestCase;
use Vfs\FileSystem;

final class LoaderTest extends TestCase
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

    public function testLoadXlsxSuccessful(): void
    {
        $entityFactory = new InternalEntityFactory();
        $optionManager = new OptionsManager(
            new StyleBuilder()
        );
        $helperFactory = new HelperFactory();
        $helperFactory->createSpecificFileSystemHelper(
            $optionManager,
            $entityFactory
        );

        $managerFactory = new ManagerFactory(
            $entityFactory,
            $helperFactory
        );
        $managerFactory->createWorkbookManager($optionManager);

        $writer = new Writer(
            $optionManager,
            new GlobalFunctionsHelper(),
            $helperFactory,
            $managerFactory
        );

        $loader = new Loader($writer);

        $pipeline = new Pipeline(
            new PipelineRunner(),
            new \ArrayIterator([
                [
                    'first name' => 'john',
                    'last name' => 'doe'
                ],
                [
                    'first name' => 'jean',
                    'last name' => 'dupont'
                ]
            ])
        );

        $writer->openToFile('vfs://test.xlsx');

        $pipeline->load($loader);
        $pipeline->run();

        $loader->load(); // TODO: check why result file is empty

//        var_dump(file_get_contents('vfs://test.xlsx'));

//        $this->assertEquals(
//            file_get_contents('tests/functional/Sheet/FingersCrossed/result-to-load.xlsx'),
//            file_get_contents('vfs://test.xlsx')
//        );
    }
}
