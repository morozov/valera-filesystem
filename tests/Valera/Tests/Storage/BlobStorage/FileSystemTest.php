<?php

namespace Valera\Tests\Storage\BlobStorage;

use Valera\Storage\BlobStorage\FileSystem as Storage;

class FileSystemTest extends AbstractTest
{
    public static function setUpBeforeClass()
    {
        $tmpDir = sys_get_temp_dir();
        self::$storage = new Storage($tmpDir . '/valera');
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        $this->markTestIncomplete();
    }
}
