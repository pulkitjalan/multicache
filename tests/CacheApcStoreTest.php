<?php

use Illuminate\Cache\ApcWrapper;
use PulkitJalan\Cache\ApcStore;

class CacheApcStoreTest extends PHPUnit_Framework_TestCase
{
    public function testGetReturnsNullWhenNotFound()
    {
        $apc = $this->getMock(ApcWrapper::class, ['get']);
        $apc->expects($this->exactly(4))->method('get')->withConsecutive(
            [$this->equalTo('foobar')],
            [$this->equalTo('foobaz')],
            [$this->equalTo('foobar')],
            [$this->equalTo('foobaz')]
        )->will($this->returnValue(null));
        $store = new ApcStore($apc, 'foo');
        $result = $store->getMulti(['bar', 'baz']);
        $this->assertNull($result['bar']);
        $this->assertNull($result['baz']);
        $result = $store->get(['bar', 'baz']);
        $this->assertNull($result['bar']);
        $this->assertNull($result['baz']);
    }

    public function testAPCValueIsReturned()
    {
        $apc = $this->getMock(ApcWrapper::class, ['get']);
        $apc->expects($this->exactly(4))->method('get')->willReturnOnConsecutiveCalls(
            $this->returnValue('bar'),
            $this->returnValue('baz'),
            $this->returnValue('bar'),
            $this->returnValue('baz')
        );
        $store = new ApcStore($apc);
        $result = $store->getMulti(['foo', 'bar']);
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('baz', $result['bar']);
        $result = $store->get(['foo', 'bar']);
        $this->assertEquals('bar', $result['foo']);
        $this->assertEquals('baz', $result['bar']);
    }

    public function testSetMethodProperlyCallsAPC()
    {
        $apc = $this->getMock(ApcWrapper::class, ['put']);
        $apc->expects($this->exactly(4))->method('put')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(60)],
            [$this->equalTo('baz'), $this->equalTo('boom'), $this->equalTo(60)],
            [$this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(60)],
            [$this->equalTo('baz'), $this->equalTo('boom'), $this->equalTo(60)]
        );
        $store = new ApcStore($apc);
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 1);
        $store->put(['foo', 'baz'], ['bar', 'boom'], 1);
    }

    public function testIncrementMethodProperlyCallsAPC()
    {
        $apc = $this->getMock(ApcWrapper::class, ['increment']);
        $apc->expects($this->exactly(4))->method('increment')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)],
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)]
        );
        $store = new ApcStore($apc);
        $store->incrementMulti(['foo' => 5, 'bar']);
        $store->increment(['foo' => 5, 'bar']);
    }

    public function testDecrementMethodProperlyCallsAPC()
    {
        $apc = $this->getMock(ApcWrapper::class, ['decrement']);
        $apc->expects($this->exactly(4))->method('decrement')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)],
            [$this->equalTo('foo'), $this->equalTo(5)],
            [$this->equalTo('bar'), $this->equalTo(1)]
        );
        $store = new ApcStore($apc);
        $store->decrementMulti(['foo' => 5, 'bar']);
        $store->decrement(['foo' => 5, 'bar']);
    }

    public function testStoreItemForeverProperlyCallsAPC()
    {
        $apc = $this->getMock(ApcWrapper::class, ['put']);
        $apc->expects($this->exactly(4))->method('put')->withConsecutive(
            [$this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(0)],
            [$this->equalTo('baz'), $this->equalTo('boom'), $this->equalTo(0)],
            [$this->equalTo('foo'), $this->equalTo('bar'), $this->equalTo(0)],
            [$this->equalTo('baz'), $this->equalTo('boom'), $this->equalTo(0)]
        );
        $store = new ApcStore($apc);
        $store->foreverMulti(['foo' => 'bar', 'baz' => 'boom']);
        $store->forever(['foo', 'baz'], ['bar', 'boom']);
    }

    public function testForgetMethodProperlyCallsAPC()
    {
        $apc = $this->getMock(ApcWrapper::class, ['delete']);
        $apc->expects($this->exactly(4))->method('delete')->withConsecutive(
            [$this->equalTo('foo')],
            [$this->equalTo('bar')],
            [$this->equalTo('foo')],
            [$this->equalTo('bar')]
        );
        $store = new ApcStore($apc);
        $store->forgetMulti(['foo', 'bar']);
        $store->forget(['foo', 'bar']);
    }
}
