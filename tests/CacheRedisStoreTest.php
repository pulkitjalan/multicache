<?php

use Mockery as m;
use PulkitJalan\Cache\RedisStore;

class CacheRedisStoreTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testGetReturnsNullWhenNotFound()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('mget')->once()->with(['prefix:foo', 'prefix:bar'])->andReturn([null, null]);
        $this->assertEquals(['foo' => null, 'bar' => null], $redis->getMany(['foo', 'bar']));
    }

    public function testRedisValueIsReturned()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('mget')->once()->with(['prefix:foo', 'prefix:baz'])->andReturn([serialize('bar'), serialize('boom')]);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $redis->getMany(['foo', 'baz']));
    }

    public function testRedisValueIsReturnedForNumerics()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('mget')->once()->with(['prefix:foo', 'prefix:bar'])->andReturn([1, 2]);
        $this->assertEquals(['foo' => 1, 'bar' => 2], $redis->getMany(['foo', 'bar']));
    }

    public function testSetMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('transaction')->once();
        $redis->putMany(['foo' => 'bar', 'baz' => 'boom'], 60);
    }

    public function testStoreItemForeverProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('mset')->once()->with([
            'prefix:foo' => serialize('bar'),
            'prefix:baz' => serialize('boom'),
        ]);
        $redis->foreverMany(['foo' => 'bar', 'baz' => 'boom'], 60);
    }

    public function testForgetMethodProperlyCallsRedis()
    {
        $redis = $this->getRedis();
        $redis->getRedis()->shouldReceive('connection')->once()->with('default')->andReturn($redis->getRedis());
        $redis->getRedis()->shouldReceive('del')->once()->with(['prefix:foo', 'prefix:bar']);
        $this->assertEquals(['foo' => true, 'bar' => true], $redis->forgetMany(['foo', 'bar']));
    }

    protected function getRedis()
    {
        return new RedisStore(m::mock('Illuminate\Redis\Database'), 'prefix');
    }
}
