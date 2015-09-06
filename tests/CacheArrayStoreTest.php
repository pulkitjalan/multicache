<?php

use PulkitJalan\Cache\ArrayStore;

class CacheArrayStoreTest extends PHPUnit_Framework_TestCase
{
    public function testItemsCanBeSetAndRetrieved()
    {
        $store = new ArrayStore;
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 10);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $store->getMulti(['foo', 'baz']));
        $store->put(['foo', 'baz'], ['bar', 'boom'], 10);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $store->get(['foo', 'baz']));
    }

    public function testStoreItemForeverProperlyStoresInArray()
    {
        $mock = $this->getMock(ArrayStore::class, ['putMulti']);
        $mock->expects($this->exactly(2))->method('putMulti')->withConsecutive(
            [$this->equalTo(['foo' => 'bar', 'baz' => 'boom']), $this->equalTo(0)],
            [$this->equalTo(['foo' => 'bar', 'baz' => 'boom']), $this->equalTo(0)]
        );
        $mock->foreverMulti(['foo' => 'bar', 'baz' => 'boom']);
        $mock->forever(['foo', 'baz'], ['bar', 'boom']);
    }

    public function testValuesCanBeIncremented()
    {
        $store = new ArrayStore;
        $store->put(['foo', 'bar'], [1, 1], 10);
        $store->incrementMulti(['foo' => 2, 'bar']);
        $results = $store->get(['foo', 'bar']);
        $this->assertEquals(3, $results['foo']);
        $this->assertEquals(2, $results['bar']);
        $store->increment(['foo' => 2, 'bar']);
        $results = $store->get(['foo', 'bar']);
        $this->assertEquals(5, $results['foo']);
        $this->assertEquals(3, $results['bar']);
    }

    public function testValuesCanBeDecremented()
    {
        $store = new ArrayStore;
        $store->put(['foo', 'bar'], [1, 1], 10);
        $store->decrementMulti(['foo' => 2, 'bar']);
        $results = $store->get(['foo', 'bar']);
        $this->assertEquals(-1, $results['foo']);
        $this->assertEquals(0, $results['bar']);
        $store->decrement(['foo' => 2, 'bar']);
        $results = $store->get(['foo', 'bar']);
        $this->assertEquals(-3, $results['foo']);
        $this->assertEquals(-1, $results['bar']);
    }

    public function testItemsCanBeRemoved()
    {
        $store = new ArrayStore;
        $store->put(['foo', 'baz'], ['bar', 'boom'], 10);
        $store->forgetMulti(['foo', 'bar']);
        $this->assertNull($store->get('foo'));
        $this->assertNull($store->get('bar'));
        $store->put(['foo', 'baz'], ['bar', 'boom'], 10);
        $store->forget(['foo', 'bar']);
        $this->assertNull($store->get('foo'));
        $this->assertNull($store->get('bar'));
    }
}
