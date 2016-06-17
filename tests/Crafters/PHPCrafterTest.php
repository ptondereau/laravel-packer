<?php

namespace Ptondereau\Tests\PackMe\Crafters;

use ConstantNull\Backstubber\FileGenerator;
use Ptondereau\PackMe\Crafters\CrafterInterface;
use Ptondereau\PackMe\Crafters\PHPCrafter;
use Ptondereau\Tests\PackMe\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Class PHPCrafterTest.
 */
class PHPCrafterTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var FileGenerator
     */
    protected $stubber;

    /**
     * @before
     */
    public function setUpDependencies()
    {
        $this->fs = new Filesystem();
        $this->stubber = new FileGenerator();
    }

    public function testConstruct()
    {
        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $this->assertInstanceOf(CrafterInterface::class, $crafter);
    }

    public function testSetter()
    {
        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $crafter->setDescription('test');
        $crafter->setAuthor(['name' => 'John Smith', 'email' => 'john@smith.com']);
        $crafter->setName('vendor/package');

        $this->assertAttributeSame('test', 'description', $crafter);
        $this->assertAttributeSame(['name' => 'John Smith', 'email' => 'john@smith.com'], 'author', $crafter);
        $this->assertAttributeSame('vendor/package', 'name', $crafter);
    }

    public function testCrafting()
    {
        if (is_dir(__DIR__.'/../output/test')) {
            $this->removeOutput();
        }

        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $crafter->setDescription('test');
        $crafter->setAuthor(['name' => 'John Smith', 'email' => 'john@smith.com']);
        $crafter->setName('vendor/package');
        $crafter->setDestination('tests/output/test');

        $crafter->craft();

        $outputpath = realpath(__DIR__.'/../output/test');

        $this->assertFileExists($outputpath.'/config/package.php');
        $this->assertFileExists($outputpath.'/src/PackageServiceProvider.php');
        $this->assertFileExists($outputpath.'/src/DummyClass.php');
        $this->assertFileExists($outputpath.'/src/Facades/DummyClass.php');
        $this->assertFileExists($outputpath.'/tests/TestCase.php');
        $this->assertFileExists($outputpath.'/tests/ServiceProviderTest.php');
        $this->assertFileExists($outputpath.'/tests/DummyClassTest.php');
        $this->assertFileExists($outputpath.'/tests/Facades/DummyClassTest.php');
        $this->assertFileExists($outputpath.'/.gitattributes');
        $this->assertFileExists($outputpath.'/.gitignore');
        $this->assertFileExists($outputpath.'/.travis.yml');
        $this->assertFileExists($outputpath.'/CHANGELOG.md');
        $this->assertFileExists($outputpath.'/CONTRIBUTING.md');
        $this->assertFileExists($outputpath.'/LICENSE');
        $this->assertFileExists($outputpath.'/README.md');
        $this->assertFileExists($outputpath.'/composer.json');
        $this->assertFileExists($outputpath.'/phpunit.xml.dist');

        $this->removeOutput();
    }

    /**
     * @expectedException  \Ptondereau\PackMe\Exception\CrafterException
     * @expectedExceptionMessage Author is not defined!
     */
    public function testExceptionWhenAuthorIsWrong()
    {
        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $crafter->setDescription('test');
        $crafter->setName('vendor/package');
        $crafter->setDestination('tests/output/test');

        $crafter->craft();
    }

    /**
     * @expectedException  \Ptondereau\PackMe\Exception\CrafterException
     * @expectedExceptionMessage Package name is not defined!
     */
    public function testExceptionWhenNameIsWrong()
    {
        $crafter = new PHPCrafter($this->stubber, $this->fs);

        $crafter->craft();
    }

    /**
     * @expectedException  \Ptondereau\PackMe\Exception\CrafterException
     * @expectedExceptionMessage Destination folder is not defined!
     */
    public function testExceptionWhenDestinationIsWrong()
    {
        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $crafter->setDescription('test');
        $crafter->setAuthor(['name' => 'John Smith', 'email' => 'john@smith.com']);
        $crafter->setName('vendor/package');

        $crafter->craft();
    }

    /**
     * @expectedException  \Ptondereau\PackMe\Exception\CrafterException
     * @expectedExceptionMessage Package already exists!
     */
    public function testExceptionWhenFolderAlreadyExists()
    {
        if (!is_dir(__DIR__.'/../output/test/')) {
            mkdir(__DIR__.'/../output/test/', 0777);
        }

        $crafter = new PHPCrafter($this->stubber, $this->fs);
        $crafter->setDescription('test');
        $crafter->setAuthor(['name' => 'John Smith', 'email' => 'john@smith.com']);
        $crafter->setName('vendor/package');
        $crafter->setDestination('tests/output/test');

        $crafter->craft();

        $this->removeOutput();
    }

    /**
     * Remove the output craft folder.
     *
     * @return void
     */
    protected function removeOutput()
    {
        $it = new \RecursiveDirectoryIterator(
            __DIR__.'/../output/test',
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir(__DIR__.'/../output/test');
    }
}
