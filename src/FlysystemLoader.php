<?php

namespace CedricZiel\TwigLoaderFlysystem;

use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Twig\Error\LoaderError;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * Provides a template loader for twig that allows to use flysystem
 * instances to load templates.
 *
 * @package CedricZiel\TwigLoaderFlysystem
 */
class FlysystemLoader implements LoaderInterface
{
    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * @var string
     */
    private string $templatePath;

    /**
     * FlysystemLoader constructor.
     *
     * @param Filesystem $filesystem
     * @param string     $templatePath
     */
    public function __construct(Filesystem $filesystem, $templatePath = '')
    {
        $this->filesystem   = $filesystem;
        $this->templatePath = $templatePath;
    }

    /**
     * Gets the source code of a template, given its name.
     *
     * @param string $name The name of the template to load
     *
     * @return Source The template source code
     *
     * @throws LoaderError When $name is not found
     */
    public function getSourceContext(string $name): Source
    {
        $this->existsOrFail($name);

        try {
            return new Source(
                $this->filesystem->read($this->resolveTemplateName($name)),
                $name
            );
        } catch (FileNotFoundException $e) {
            throw new LoaderError('File not found.', -1, null, $e);
        }
    }

    /**
     * Gets the cache key to use for the cache for a given template name.
     *
     * @param string $name The name of the template to load
     *
     * @return string The cache key
     *
     * @throws LoaderError When $name is not found
     */
    public function getCacheKey(string $name): string
    {
        $this->existsOrFail($name);

        return $name;
    }

    /**
     * Returns true if the template is still fresh.
     *
     * @param string $name The template name
     * @param int    $time Timestamp of the last modification time of the
     *                     cached template
     *
     * @return bool true if the template is fresh, false otherwise
     *
     * @throws LoaderError When $name is not found
     */
    public function isFresh(string $name, int $time): bool
    {
        $this->existsOrFail($name);

        // getTimestamp() throws a FileNotFoundException, however we've already checked that the path exists.
        // Let's handle it anyway just in case.
        try {
            return $time >= $this->filesystem->getTimestamp($name);
        } catch (FileNotFoundException $e) {
            throw new LoaderError('File not found.', -1, null, $e);
        }
    }

    /**
     * Check if we have the source code of a template, given its name.
     *
     * @param string $name The name of the template to check if we can load
     *
     * @return bool If the template source code is handled by this loader or not
     */
    public function exists(string $name): bool
    {
        try {
            $this->existsOrFail($name);
        } catch (LoaderError $loader_error) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the underlying flysystem contains a file of the given name.
     *
     * @param string $name
     *
     * @return void
     *
     * @throws LoaderError
     */
    private function existsOrFail(string $name): void
    {
        $resolvedTemplateName = $this->resolveTemplateName($name);

        if (!$this->filesystem->has($resolvedTemplateName)) {
            throw new LoaderError('Template could not be found on the given filesystem');
        }

        // getMetadata() throws a FileNotFoundException, however we've already checked that the path exists.
        // Let's handle it anyway just in case.
        try {
            $metadata = $this->filesystem->getMetadata($resolvedTemplateName);
            if ('dir' === $metadata['type']) {
                throw new LoaderError('Cannot use directory as template');
            }
        } catch (FileNotFoundException $e) {
            throw new LoaderError('File not found.', -1, null, $e);
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private function resolveTemplateName(string $name): string
    {
        $prefix = $this->templatePath;
        if ($this->templatePath !== null && $this->templatePath !== '') {
            $prefix = rtrim($prefix, '/') . '/';
        }

        return $prefix . $name;
    }
}
