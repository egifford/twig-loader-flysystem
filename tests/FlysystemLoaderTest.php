<?php

namespace CedricZiel\TwigLoaderFlysystem\Test;

use CedricZiel\TwigLoaderFlysystem\FlysystemLoader;
use League\Flysystem\Filesystem;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Twig\Error\LoaderError;

/**
 * @package CedricZiel\TwigLoaderFlysystem\Test
 */
class FlysystemLoaderTest extends TestCase
{
    /**
     * @test
     *
     * @throws LoaderError
     */
    public function loaderCanLoadTemplatesByPath(): void
    {
        $templateName = 'test/Object.twig';
        $templateCode = '{{ template }}';

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'has',
                'getMetadata',
                'read',
            ])
            ->getMock();
        $filesystem
            ->expects(self::atLeastOnce()) // This a an assertion.
            ->method('has')
            ->with($templateName) // This a an assertion.
            ->willReturn(true);
        $filesystem
            ->method('getMetadata')
            ->willReturn(['type' => 'file']);
        $filesystem
            ->method('read')
            ->with($templateName) // This a an assertion.
            ->willReturn($templateCode);

        $loader = new FlysystemLoader($filesystem);
        $source = $loader->getSourceContext($templateName);

        self::assertSame($templateCode, $source->getCode());
        self::assertSame($templateName, $source->getName());
    }

    /**
     * @test
     */
    public function throwsLoaderErrorWhenTemplateNotFound(): void
    {
        $this->expectException(LoaderError::class);
        $this->expectExceptionMessage('Template could not be found on the given filesystem');

        $templateName = 'test/Object.twig';

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'has',
            ])
            ->getMock();
        $filesystem
            ->expects(self::once()) // This a an assertion.
            ->method('has')
            ->with($templateName) // This a an assertion.
            ->willReturn(false);

        $loader = new FlysystemLoader($filesystem);
        $loader->getSourceContext($templateName);
    }

    /**
     * @test
     *
     * @throws LoaderError
     */
    public function canCreateCacheKey(): void
    {
        $templateName = 'test/Object.twig';

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'has',
                'getMetadata',
            ])
            ->getMock();
        $filesystem
            ->expects(self::atLeastOnce()) // This a an assertion.
            ->method('has')
            ->with($templateName) // This a an assertion.
            ->willReturn(true);
        $filesystem
            ->method('getMetadata')
            ->willReturn(['type' => 'file']);

        $loader = new FlysystemLoader($filesystem);

        self::assertEquals($templateName, $loader->getCacheKey($templateName));
    }

    /**
     * @test
     *
     * @throws LoaderError
     */
    public function canDetermineIfATemplateIsStillFresh(): void
    {
        $templateName = 'test/Object.twig';
        $timestamp    = 1233;

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'has',
                'getMetadata',
                'getTimestamp',
            ])
            ->getMock();
        $filesystem
            ->expects(self::atLeastOnce()) // This a an assertion.
            ->method('has')
            ->with($templateName) // This a an assertion.
            ->willReturn(true);
        $filesystem
            ->method('getMetadata')
            ->willReturn(['type' => 'file']);
        $filesystem
            ->method('getTimestamp')
            ->willReturn($timestamp);

        $loader = new FlysystemLoader($filesystem);

        self::assertTrue($loader->isFresh($templateName, $timestamp + 1));
    }

    /**
     * @test
     *
     * @throws LoaderError
     */
    public function aFilesystemPrefixCanBeUsed(): void
    {
        $prefix               = 'templates';
        $templateName         = 'test/Object.twig';
        $templateNamePrefixed = $prefix . '/' . $templateName;
        $templateCode         = '{{ template }}';

        /** @var Filesystem|MockObject $filesystem */
        $filesystem = $this->getMockBuilder(Filesystem::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'has',
                'getMetadata',
                'read',
            ])
            ->getMock();
        $filesystem
            ->expects(self::atLeastOnce()) // This a an assertion.
            ->method('has')
            ->with($templateNamePrefixed) // This a an assertion.
            ->willReturn(true);
        $filesystem
            ->method('getMetadata')
            ->willReturn(['type' => 'file']);
        $filesystem
            ->expects(self::atLeastOnce()) // This a an assertion.
            ->method('read')
            ->with($templateNamePrefixed) // This a an assertion.
            ->willReturn($templateCode);

        $loader = new FlysystemLoader($filesystem, $prefix);

        $source = $loader->getSourceContext($templateName);
        self::assertSame($templateName, $source->getName());
        self::assertSame($templateCode, $source->getCode());
    }
}
