<?php

use PulkitJalan\Cache\MemcachedStore;

class CacheMemcachedStoreTest extends PHPUnit_Framework_TestCase
{
    public function testGetReturnsNullWhenNotFound()
    {
        $memcache = $this->getMock('Memcached', ['getMulti', 'getResultCode']);
        $memcache->expects($this->exactly(2))->method('getMulti')->with($this->equalTo(['foo:bar', 'foo:baz']))->will($this->returnValue(null));
        $memcache->expects($this->exactly(2))->method('getResultCode')->will($this->returnValue(1));
        $store = new MemcachedStore($memcache, 'foo');
        $values = $store->getMulti(['bar', 'baz']);
        $this->assertNull($values['bar']);
        $this->assertNull($values['baz']);
        $values = $store->get(['bar', 'baz']);
        $this->assertNull($values['bar']);
        $this->assertNull($values['baz']);
    }

    public function testMemcacheValueIsReturned()
    {
        $memcache = $this->getMock('Memcached', ['getMulti', 'getResultCode']);
        $memcache->expects($this->exactly(2))->method('getMulti')->will($this->returnValue(['foo' => 'bar', 'baz' => 'boom']));
        $memcache->expects($this->exactly(2))->method('getResultCode')->will($this->returnValue(0));
        $store = new MemcachedStore($memcache);
        $values = $store->getMulti(['foo', 'baz']);
        $this->assertEquals('bar', $values['foo']);
        $this->assertEquals('boom', $values['baz']);
        $values = $store->get(['foo', 'baz']);
        $this->assertEquals('bar', $values['foo']);
        $this->assertEquals('boom', $values['baz']);
    }

    public function testSetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['setMulti']);
        $memcache->expects($this->exactly(2))->method('setMulti')->with($this->equalTo(['foo:foo' => 'bar', 'foo:baz' => 'boom']), $this->equalTo(60));
        $store = new MemcachedStore($memcache, 'foo');
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 1);
        $store->put(['foo', 'baz'], ['bar', 'boom'], 1);
    }

    public function testIncrementMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['increment']);
        $memcache->expects($this->exactly(4))->method('increment')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)],
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)]
        );
        $store = new MemcachedStore($memcache);
        $store->incrementMulti(['foo' => 5, 'bar']);
        $store->increment(['foo' => 5, 'bar']);
    }

    public function testDecrementMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['decrement']);
        $memcache->expects($this->exactly(4))->method('decrement')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)],
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)]
        );
        $store = new MemcachedStore($memcache);
        $store->decrementMulti(['foo' => 5, 'bar']);
        $store->decrement(['foo' => 5, 'bar']);
    }

    public function testStoreItemForeverProperlyCallsMemcached()
    {
        $memcache = $this->getMock('Memcached', ['setMulti']);
        $memcache->expects($this->exactly(2))->method('setMulti')->with($this->equalTo(['foo:foo' => 'bar', 'foo:baz' => 'boom']), $this->equalTo(0));
        $store = new MemcachedStore($memcache, 'foo');
        $store->foreverMulti(['foo' => 'bar', 'baz' => 'boom']);
        $store->forever(['foo', 'baz'], ['bar', 'boom']);
    }

    public function testForgetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['deleteMulti']);
        $memcache->expects($this->exactly(2))->method('deleteMulti')->with($this->equalTo(['foo:bar', 'foo:baz']));
        $store = new MemcachedStore($memcache, 'foo');
        $store->forgetMulti(['bar', 'baz']);
        $store->forget(['bar', 'baz']);
    }
}
