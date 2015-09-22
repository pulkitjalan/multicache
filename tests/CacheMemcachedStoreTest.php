<?php

use PulkitJalan\Cache\MemcachedStore;

class CacheMemcachedStoreTest extends PHPUnit_Framework_TestCase
{
    public function testGetReturnsNullWhenNotFound()
    {
        $memcache = $this->getMock('Memcached', ['getMulti', 'getResultCode']);
        $memcache->expects($this->once())->method('getMulti')->with($this->equalTo(['foo:bar', 'foo:baz']))->will($this->returnValue(null));
        $memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(1));
        $store = new MemcachedStore($memcache, 'foo');
        $values = $store->getMany(['bar', 'baz']);
        $this->assertNull($values['bar']);
        $this->assertNull($values['baz']);
    }

    public function testMemcacheValueIsReturned()
    {
        $memcache = $this->getMock('Memcached', ['getMulti', 'getResultCode']);
        $memcache->expects($this->once())->method('getMulti')->will($this->returnValue(['foo' => 'bar', 'baz' => 'boom']));
        $memcache->expects($this->once())->method('getResultCode')->will($this->returnValue(0));
        $store = new MemcachedStore($memcache);
        $values = $store->getMany(['foo', 'baz']);
        $this->assertEquals('bar', $values['foo']);
        $this->assertEquals('boom', $values['baz']);
    }

    public function testSetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['setMulti']);
        $memcache->expects($this->once())->method('setMulti')->with($this->equalTo(['foo:foo' => 'bar', 'foo:baz' => 'boom']), $this->equalTo(60));
        $store = new MemcachedStore($memcache, 'foo');
        $store->putMany(['foo' => 'bar', 'baz' => 'boom'], 1);
    }

    public function testStoreItemForeverProperlyCallsMemcached()
    {
        $memcache = $this->getMock('Memcached', ['setMulti']);
        $memcache->expects($this->once())->method('setMulti')->with($this->equalTo(['foo:foo' => 'bar', 'foo:baz' => 'boom']), $this->equalTo(0));
        $store = new MemcachedStore($memcache, 'foo');
        $store->foreverMany(['foo' => 'bar', 'baz' => 'boom']);
    }

    public function testForgetMethodProperlyCallsMemcache()
    {
        $memcache = $this->getMock('Memcached', ['deleteMulti']);
        $memcache->expects($this->once())->method('deleteMulti')->with($this->equalTo(['foo:bar', 'foo:baz']))->will($this->returnValue(true));
        $store = new MemcachedStore($memcache, 'foo');
        $values = $store->forgetMany(['bar', 'baz']);
        $this->assertTrue($values['bar']);
        $this->assertTrue($values['baz']);

        $memcache = $this->getMock('Memcached', ['delete']);
        $memcache->expects($this->exactly(2))->method('delete')->withConsecutive(
            [$this->equalTo('foo:bar')],
            [$this->equalTo('foo:baz')]
        );
        $store = new MemcachedStore($memcache, 'foo');
        $store->forgetMany(['bar', 'baz']);
    }
}
