<?php
namespace Gungnir\Core\Tests;

use org\bovigo\vfs\vfsStream;
use Gungnir\Core\Autoloader;

class AutoloaderTest extends \PHPUnit_Framework_TestCase
{

    private $root = null;

    public function setUp()
    {
        parent::setUp();
        $root        = vfsStream::setup('project');
        $application = vfsStream::newDirectory('application');
        $vendor = vfsStream::newDirectory('vendor');

        $classes     = vfsStream::newDirectory('classes');

        $gungnir     = vfsStream::newDirectory('Gungnir');
        $otherNamespace = vfsStream::newDirectory('OtherNamespace');

        $core        = vfsStream::newDirectory('Core');

        vfsStream::copyFromFileSystem(dirname(dirname(__FILE__)) . '/src', $core);
        vfsStream::copyFromFileSystem(dirname(dirname(__FILE__)) . '/src', $otherNamespace);

        $gungnir->addChild($core);

        $classes->addChild($gungnir);
        $application->addChild($classes);

        $classes->addChild($otherNamespace);
        $vendor->addChild($classes);

        $root->addChild($application);
        $root->addChild($vendor);

        $content = file_get_contents($otherNamespace->url() . '/Config.php');
        $content = str_replace('namespace Gungnir\Core','namespace Gungnir\OtherNamespace', $content);

        file_put_contents($otherNamespace->url() . '/Config.php', $content);

        $this->root = $root;
    }

    public function testItCanLoadApplicationClasses()
    {
        $autoloader = new Autoloader($this->root->url());
        $autoloader->classLoader('\Gungnir\Core\Config');
    }

    public function testPsr4PrefixesCanBeAddedAndLoaded()
    {
        $autoloader = new Autoloader($this->root->url());
        $autoloader->psr4Prefix('Gungnir\OtherNamespace', $this->root->url() . '/vendor/classes/OtherNamespace');
        $autoloader->classLoader('\Gungnir\OtherNamespace\Config');
    }
}