<?php

namespace Valera\Tests\Storage\BlobStorage;

use Valera\Storage\BlobStorage\FileSystem as Storage;
use Valera\Tests\Value\Helper;

/**
 * @requires extension mbstring
 */
class FileSystemTest extends AbstractTest
{
    public static function setUpBeforeClass()
    {
        $tmpDir = sys_get_temp_dir();
        self::$storage = new Storage($tmpDir . '/valera');
        parent::setUpBeforeClass();
    }

    /**
     * @param string $original
     * @param string $expected
     *
     * @test
     * @dataProvider truncateProvider
     */
    public function truncate($original, $expected)
    {
        $re = new \ReflectionMethod(self::$storage, 'truncate');
        $re->setAccessible(true);
        $resource = Helper::getResource();
        $truncated = $re->invokeArgs(self::$storage, array($original, $resource));
        $this->assertEquals($expected, $truncated);
        $this->assertEquals($expected, $truncated);
    }

    public static function truncateProvider()
    {
        return array(
            array(
                'short-file-name.jpg',
                'short-file-name.jpg',
            ),
            array(
                'a-very-long-file-name-of-more-than-255-characters-containing-file-extension-at-the-end'
                    . str_repeat('-lo-ooo-ong', 16) . '.jpg',
                'a-very-long-file-name-of-more-than-255-characters-containing-file-extension-at-the-end'
                    . str_repeat('-lo-ooo-ong', 14) . '-lo-64bb92d.jpg',
            ),
            array(
                str_repeat('длинное-название-', 15) . 'файла.tiff',
                str_repeat('длинное-название-', 14) . 'длин-64bb92d.tiff',
            ),
        );
    }
}
