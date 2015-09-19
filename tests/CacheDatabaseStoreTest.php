<?php

use Mockery as m;
use Illuminate\Support\Collection;
use PulkitJalan\Cache\DatabaseStore;

class CacheDatabaseStoreTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testNullIsReturnedWhenItemNotFound()
    {
        $store = $this->getStore();
        $table = m::mock('StdClass');
        $store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
        $table->shouldReceive('whereIn')->once()->with('key', ['prefixfoo', 'prefixbar'])->andReturn($table);
        $table->shouldReceive('get')->once()->andReturn(new Collection([]));

        $this->assertEquals(['foo' => null, 'bar' => null], $store->getMulti(['foo', 'bar']));
    }

    public function testNullIsReturnedAndItemDeletedWhenItemIsExpired()
    {
        $store = $this->getMock(DatabaseStore::class, ['forgetMulti'], $this->getMocks());
        $table = m::mock('StdClass');
        $store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
        $table->shouldReceive('whereIn')->once()->with('key', ['prefixfoo', 'prefixbar'])->andReturn($table);
        $table->shouldReceive('get')->once()->andReturn(new Collection([
            (object) ['key' => 'prefixfoo', 'expiration' => 1],
            (object) ['key' => 'prefixbar', 'expiration' => 1],
        ]));
        $store->expects($this->once())->method('forgetMulti')->with($this->equalTo(['foo', 'bar']))->will($this->returnValue(null));

        $this->assertEquals(['foo' => null, 'bar' => null], $store->getMulti(['foo', 'bar']));
    }

    public function testDecryptedValueIsReturnedWhenItemIsValid()
    {
        $store = $this->getStore();
        $table = m::mock('StdClass');
        $store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
        $table->shouldReceive('whereIn')->once()->with('key', ['prefixfoo', 'prefixbaz'])->andReturn($table);
        $table->shouldReceive('get')->once()->andReturn(new Collection([
            (object) ['key' => 'prefixfoo', 'value' => 'bar', 'expiration' => 999999999999999],
            (object) ['key' => 'prefixbaz', 'value' => 'boom', 'expiration' => 999999999999999],
        ]));
        $store->getEncrypter()->shouldReceive('decrypt')->once()->with('bar')->andReturn('bar');
        $store->getEncrypter()->shouldReceive('decrypt')->once()->with('boom')->andReturn('boom');

        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $store->getMulti(['foo', 'baz']));
    }

    public function testEncryptedValueIsInsertedWhenNoExceptionsAreThrown()
    {
        $store = $this->getMock(DatabaseStore::class, ['getTime', 'forgetMulti'], $this->getMocks());
        $table = m::mock('StdClass');
        $store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
        $store->getEncrypter()->shouldReceive('encrypt')->once()->with('bar')->andReturn('bar');
        $store->getEncrypter()->shouldReceive('encrypt')->once()->with('boom')->andReturn('boom');
        $store->expects($this->once())->method('getTime')->will($this->returnValue(1));
        $table->shouldReceive('insert')->once()->with([
            ['key' => 'prefixfoo', 'value' => 'bar', 'expiration' => 61],
            ['key' => 'prefixbaz', 'value' => 'boom', 'expiration' => 61]
        ]);
        $store->expects($this->once())->method('forgetMulti')->with($this->equalTo(['foo', 'baz']))->will($this->returnValue(null));

        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 1);
    }

    public function testForeverCallsStoreItemWithReallyLongTime()
    {
        $store = $this->getMock(DatabaseStore::class, ['putMulti'], $this->getMocks());
        $store->expects($this->once())->method('putMulti')->with($this->equalTo(['foo' => 'bar', 'baz' => 'boom']), $this->equalTo(5256000));
        $store->foreverMulti(['foo' => 'bar', 'baz' => 'boom']);
    }

    public function testItemsMayBeRemovedFromCache()
    {
        $store = $this->getStore();
        $table = m::mock('StdClass');
        $store->getConnection()->shouldReceive('table')->once()->with('table')->andReturn($table);
        $table->shouldReceive('whereIn')->once()->with('key', ['prefixfoo', 'prefixbar'])->andReturn($table);
        $table->shouldReceive('delete')->once();

        $store->forgetMulti(['foo', 'bar']);
    }

    protected function getStore()
    {
        return new DatabaseStore(m::mock('Illuminate\Database\ConnectionInterface'), m::mock('Illuminate\Contracts\Encryption\Encrypter'), 'table', 'prefix');
    }

    protected function getMocks()
    {
        return [m::mock('Illuminate\Database\ConnectionInterface'), m::mock('Illuminate\Contracts\Encryption\Encrypter'), 'table', 'prefix'];
    }
}
