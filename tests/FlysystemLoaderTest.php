<?php

namespace CedricZiel\TwigLoaderFlysystem\Test;

use CedricZiel\TwigLoaderFlysystem\FlysystemLoader;
use League\Flysystem\AdapterInterface;
use League\Flysystem\File;
use League\Flysystem\Filesystem;
use League\Flysystem\Handler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @package CedricZiel\TwigLoaderFlysystem\Test
 */
class FlysystemLoaderTest extends TestCase
{
    /**
     * @test
     */
    public function loaderCanLoadTemplatesByPath(): void
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $loader->getSource('test/Object.twig');
    }

    /**
     * @expectedException \Twig_Error_Loader
     * @test
     */
    public function throwsLoaderErrorWhenTemplateNotFount(): void
    {
        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->expects($this->once())
            ->method('has')
            ->willReturn(false);

        $loader = new FlysystemLoader($filesystem);

        $loader->getSource('test/Object.twig');
    }

    /**
     * @test
     */
    public function canCreateCacheKey(): void
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $cacheKey = 'test/Object.twig';
        $this->assertEquals($cacheKey, $loader->getCacheKey($cacheKey));
    }

    /**
     * @test
     */
    public function canDetermineIfATemplateIsStillFresh(): void
    {
        $templateFile = $this->getMockBuilder(File::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);
        $templateFile
            ->method('getTimestamp')
            ->willReturn(1233);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem);

        $templateFile = 'test/Object.twig';
        $this->assertTrue($loader->isFresh($templateFile, 1234));
    }

    /**
     * @test
     */
    public function aFilesystemPrefixCanBeUsed(): void
    {
        $templateFile = $this->getMockBuilder(Handler::class)
            ->getMock();
        $templateFile
            ->method('isDir')
            ->willReturn(false);

        $filesystemAdapter = $this->getMockBuilder(AdapterInterface::class)
            ->getMock();
        $filesystemAdapter
            ->method('read')
            ->willReturn($templateFile);

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->setMethods(['has', 'get', 'getAdapter', 'read'])
            ->getMock();
        $filesystem
            ->method('getAdapter')
            ->willReturn($filesystemAdapter);
        $filesystem
            ->method('read')
            ->willReturn('{{ template }}');
        $filesystem
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturn(true);
        $filesystem
            ->method('get')
            ->with('templates/test/Object.twig')
            ->willReturn($templateFile);

        $loader = new FlysystemLoader($filesystem, 'templates');

        $loader->getSource('test/Object.twig');
    }
}
