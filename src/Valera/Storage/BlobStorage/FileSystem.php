<?php

namespace Valera\Storage\BlobStorage;

use Valera\Resource;
use Valera\Storage\BlobStorage;

class FileSystem implements BlobStorage
{
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
        $path = $this->getPath($resource);
        $dir = dirname($path);
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        file_put_contents($path, $contents);

        return $path;
    }

    public function retrieve(Resource $resource)
    {
        $path = $this->getPath($resource);
        if (file_exists($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    public function delete(Resource $resource)
    {
        $path = $this->getPath($resource);
        if (file_exists($path)) {
            unlink($path);
        }
    }

    protected function getPath(Resource $resource)
    {
        $url = $resource->getUrl();
        $url = preg_replace('/^[a-z0-9]+:\/\//', '', $url);
        $sections = explode('/', $url);
        $sections = array_map(function ($section) {
            return rawurldecode($section);
        }, $sections);
        array_unshift($sections, $this->root);

        $last = end($sections);
        if ($last === '') {
            array_splice($sections, -1, 1, $this->indexFileName);
        }

        return implode(DIRECTORY_SEPARATOR, $sections);
    }

    public function clean()
    {
        if (file_exists($this->root)) {
            exec('rm -r ' . escapeshellarg($this->root));
        }
    }

    public function count()
    {
        return 0;
    }
}
