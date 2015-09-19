<?php

use PulkitJalan\Cache\ArrayStore;

class CacheArrayStoreTest extends PHPUnit_Framework_TestCase
{
    public function testItemsCanBeSetAndRetrieved()
    {
        $store = new ArrayStore;
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 10);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $store->getMulti(['foo', 'baz']));
        $this->assertEquals('bar', $store->get('foo'));
        $this->assertEquals('boom', $store->get('baz'));
    }

    public function testStoreItemForeverProperlyStoresInArray()
    {
        $mock = $this->getMock(ArrayStore::class, ['putMulti']);
        $mock->expects($this->once())->method('putMulti')->with($this->equalTo(['foo' => 'bar', 'baz' => 'boom']), $this->equalTo(0));
        $mock->foreverMulti(['foo' => 'bar', 'baz' => 'boom']);
    }

    public function testItemsCanBeRemoved()
    {
        $store = new ArrayStore;
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 10);
        $store->forgetMulti(['foo', 'baz']);
        $this->assertEquals(['foo' => null, 'baz' => null], $store->getMulti(['foo', 'baz']));
        $this->assertNull($store->get('foo'));
        $this->assertNull($store->get('baz'));
    }
}
