<?php

use Mockery as m;
use Carbon\Carbon;
use PulkitJalan\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use PulkitJalan\Cache\Contracts\StoreMulti;

class CacheRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }

    public function testGetReturnsValueFromCache()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->getMulti(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->get(['foo', 'baz']));

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->once()->with(['foo', 'baz'])->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->getMulti(['foo', 'baz']));

        $repo->getStore()->shouldReceive('getMulti')->once()->with(['foo', 'baz'])->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->get(['foo', 'baz']));
    }

    public function testDefaultValueIsReturned()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->getMulti(['foo', 'baz'], 'bar'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->get(['foo', 'baz'], 'bar'));

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->with(['foo', 'baz'])->andReturn(['foo' => null, 'baz' => null]);
        $this->assertEquals(['foo' => null, 'baz' => null], $repo->getMulti(['foo', 'baz']));
    }

    public function testHasMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $this->assertEquals(['foo' => false, 'bar' => false], $repo->has(['foo', 'bar']));

        $repo->getStore()->shouldReceive('get')->twice()->andReturn('baz');
        $this->assertEquals(['foo' => true, 'bar' => true], $repo->has(['foo', 'bar']));

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn(null);
        $repo->getStore()->shouldReceive('get')->once()->with('bar')->andReturn('bar');

        $this->assertTrue($repo->has('bar'));
        $this->assertFalse($repo->has('foo'));

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => null, 'bar' => null]);
        $this->assertEquals(['foo' => false, 'bar' => false], $repo->has(['foo', 'bar']));

        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => 'baz', 'bar' => 'boom']);
        $this->assertEquals(['foo' => true, 'bar' => true], $repo->has(['foo', 'bar']));
    }

    public function testAddMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->addMulti(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->add(['foo', 'baz'], ['bar', 'boom'], 10);

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);

        $repo->add('foo', 'bar', 10);

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMulti')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->addMulti(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMulti')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->add(['foo', 'baz'], ['bar', 'boom'], 10);
    }

    public function testPullMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->andReturn('boom');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('baz')->andReturn(true);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pullMulti(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->andReturn('boom');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('baz')->andReturn(true);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pull(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);

        $this->assertEquals('bar', $repo->pull('foo'));

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $repo->getStore()->shouldReceive('forgetMulti')->once()->with(['foo', 'baz'])->andReturn(['foo' => true, 'baz' => true]);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pullMulti(['foo', 'baz']));

        $repo->getStore()->shouldReceive('getMulti')->once()->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $repo->getStore()->shouldReceive('forgetMulti')->once()->with(['foo', 'baz'])->andReturn(['foo' => true, 'baz' => true]);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pull(['foo', 'baz']));
    }

    public function testForgetMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('bar')->andReturn(false);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forgetMulti(['foo', 'bar']));

        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('bar')->andReturn(false);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forget(['foo', 'bar']));

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('forgetMulti')->once()->with(['foo', 'bar'])->andReturn(['foo' => true, 'bar' => false]);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forgetMulti(['foo', 'bar']));

        $repo->getStore()->shouldReceive('forgetMulti')->once()->with(['foo', 'bar'])->andReturn(['foo' => true, 'bar' => false]);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forget(['foo', 'bar']));
    }

    public function testRememberMethodCallsPutAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'bar', 10);
        $result = $repo->rememberMulti(['foo', 'baz'], 10, function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'bar', 10);
        $result = $repo->remember(['foo', 'baz'], 10, function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar'], 10);
        $result = $repo->rememberMulti(['foo', 'baz'], 10, function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar'], 10);
        $result = $repo->remember(['foo', 'baz'], 10, function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);
    }

    public function testRememberForeverMethodCallsForeverAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'bar');
        $result = $repo->rememberForeverMulti(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'bar');
        $result = $repo->rememberForever(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar']);
        $result = $repo->rememberForeverMulti(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar']);
        $result = $repo->rememberForever(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);
    }

    public function testSearMethodCallsForeverAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'bar');
        $result = $repo->searMulti(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'bar');
        $result = $repo->sear(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo = $this->getRepository(StoreMulti::class);
        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar']);
        $result = $repo->searMulti(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);

        $repo->getStore()->shouldReceive('getMulti')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMulti')->once()->with(['foo' => 'bar', 'baz' => 'bar']);
        $result = $repo->sear(['foo', 'baz'], function () { return 'bar'; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $result);
    }

    protected function getRepository($contract = Store::class)
    {
        return new Repository(m::mock($contract));
    }
}
