<?php

namespace Valera\Storage\BlobStorage;

use Valera\Resource;
use Valera\Storage\BlobStorage;

class FileSystem implements BlobStorage
{
    const MAX_NAME = 255;

    protected $root;

    private $indexFileName = 'index.dat';

    public function __construct($root)
    {
        $this->root = $root;
    }

    /**
     * {@inheritDoc}
     */
    public function create(Resource $resource, $contents)
    {
        if ($this->isStored($resource)) {
            throw new \DomainException('Blob already exists');
        }

        $path = $this->getPath($resource);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $contents);

        return $path;
    }

    public function isStored(Resource $resource)
    {
        $path = $this->getPath($resource);
        return file_exists($path);
    }

    public function retrieve(Resource $resource)
    {
        if ($this->isStored($resource)) {
            $path = $this->getPath($resource);
            return file_get_contents($path);
        }

        return null;
    }

    public function delete(Resource $resource)
    {
        if ($this->isStored($resource)) {
            $path = $this->getPath($resource);
            unlink($path);
        }
    }

    public function getPath(Resource $resource)
    {
        $url = $resource->getUrl();
        $url = preg_replace('/^[a-z0-9]+:\/\//', '', $url);
        $sections = explode('/', $url);
        $sections = array_map(function ($section) use ($resource) {
            $section = rawurldecode($section);
            $section = $this->truncate($section, $resource);
            return rawurldecode($section);
        }, $sections);
        array_unshift($sections, $this->root);

        $last = end($sections);
        if ($last === '') {
            array_splice($sections, -1, 1, $this->indexFileName);
        }

        return implode(DIRECTORY_SEPARATOR, $sections);
    }

    protected function truncate($fileName, Resource $resource)
    {
        if (strlen($fileName) > self::MAX_NAME) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $extensionLength = strlen($extension);
            if ($extensionLength > 0 && $extensionLength <= 4) {
                $suffix = '.' . $extension;
            } else {
                $suffix = '';
            }
            $fileName = substr($fileName, 0, self::MAX_NAME - 8 - strlen($suffix))
                . '-' . substr($resource->getHash(), 0, 7) . $suffix;
        }

        return $fileName;
    }

    public function clean()
    {
        if (file_exists($this->root)) {
            exec('rm -r ' . escapeshellarg($this->root));
        }
    }

    public function count()
    {
        if (!file_exists($this->root)) {
            return 0;
        }

        $it = new \FilesystemIterator($this->root);
        return iterator_count($it);
    }
}
