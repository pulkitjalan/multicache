<?php

use PulkitJalan\Cache\FileStore;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class CacheFileStoreTest extends PHPUnit_Framework_TestCase
{
    public function testNullIsReturnedIfFileDoesntExist()
    {
        $files = $this->mockFilesystem();
        $files->expects($this->exactly(4))->method('get')->will($this->throwException(new FileNotFoundException()));
        $store = new FileStore($files, __DIR__);
        $values = $store->getMulti(['foo', 'bar']);
        $this->assertNull($values['foo']);
        $this->assertNull($values['bar']);
        $values = $store->get(['foo', 'bar']);
        $this->assertNull($values['foo']);
        $this->assertNull($values['bar']);
    }

    public function testPutCreatesMissingDirectories()
    {
        $files = $this->mockFilesystem();
        $md5 = md5('foo');
        $full_dir = __DIR__.'/'.substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $md5_2 = md5('bar');
        $full_dir_2 = __DIR__.'/'.substr($md5_2, 0, 2).'/'.substr($md5_2, 2, 2);
        $files->expects($this->exactly(4))->method('makeDirectory')->withConsecutive(
            [$this->equalTo($full_dir), $this->equalTo(0777), $this->equalTo(true)],
            [$this->equalTo($full_dir_2), $this->equalTo(0777), $this->equalTo(true)],
            [$this->equalTo($full_dir), $this->equalTo(0777), $this->equalTo(true)],
            [$this->equalTo($full_dir_2), $this->equalTo(0777), $this->equalTo(true)]
        );
        $files->expects($this->exactly(4))->method('put')->withConsecutive(
            [$this->equalTo($full_dir.'/'.$md5)],
            [$this->equalTo($full_dir_2.'/'.$md5_2)],
            [$this->equalTo($full_dir.'/'.$md5)],
            [$this->equalTo($full_dir_2.'/'.$md5_2)]
        );
        $store = new FileStore($files, __DIR__);
        $store->putMulti(['foo' => '0000000000', 'bar' => '0000000000'], 0);
        $store->put(['foo', 'bar'], ['0000000000', '0000000000'], 0);
    }

    public function testValidItemReturnsContents()
    {
        $files = $this->mockFilesystem();
        $contents = '9999999999'.serialize('Hello World');
        $contents2 = '9999999999'.serialize('Hello World 2');
        $files->expects($this->exactly(4))->method('get')->willReturnOnConsecutiveCalls(
            $this->returnValue($contents),
            $this->returnValue($contents2),
            $this->returnValue($contents),
            $this->returnValue($contents2)
        );
        $store = new FileStore($files, __DIR__);
        $values = $store->getMulti(['foo', 'bar']);
        $this->assertEquals('Hello World', $values['foo']);
        $this->assertEquals('Hello World 2', $values['bar']);
        $values = $store->get(['foo', 'bar']);
        $this->assertEquals('Hello World', $values['foo']);
        $this->assertEquals('Hello World 2', $values['bar']);
    }

    public function testRemoveDeletesFileDoesntExist()
    {
        $files = $this->mockFilesystem();
        $md5 = md5('foobull');
        $cache_dir = substr($md5, 0, 2).'/'.substr($md5, 2, 2);
        $md5_2 = md5('foobull2');
        $cache_dir_2 = substr($md5_2, 0, 2).'/'.substr($md5_2, 2, 2);
        $files->expects($this->exactly(4))->method('exists')->withConsecutive(
            [$this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5)],
            [$this->equalTo(__DIR__.'/'.$cache_dir_2.'/'.$md5_2)],
            [$this->equalTo(__DIR__.'/'.$cache_dir.'/'.$md5)],
            [$this->equalTo(__DIR__.'/'.$cache_dir_2.'/'.$md5_2)]
        )->will($this->returnValue(false));
        $store = new FileStore($files, __DIR__);
        $store->forgetMulti(['foobull', 'foobull2']);
        $store->forget(['foobull', 'foobull2']);
    }

    protected function mockFilesystem()
    {
        return $this->getMock('Illuminate\Filesystem\Filesystem');
    }
}
