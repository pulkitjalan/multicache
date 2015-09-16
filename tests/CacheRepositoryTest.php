<?php

use Mockery as m;
use Carbon\Carbon;

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
    }

    public function testDefaultValueIsReturned()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->getMulti(['foo', 'baz'], 'bar'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->get(['foo', 'baz'], 'bar'));
    }

    public function testHasMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $this->assertEquals(['foo' => false, 'bar' => false], $repo->has(['foo', 'bar']));

        $repo->getStore()->shouldReceive('get')->twice()->andReturn('baz');
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
    }

    protected function getRepository()
    {
        return new PulkitJalan\Cache\Repository(m::mock('Illuminate\Contracts\Cache\Store'));
    }
}
