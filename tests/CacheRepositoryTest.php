<?php

use PulkitJalan\Cache\Repository;
use Illuminate\Contracts\Cache\Store;
use PulkitJalan\Cache\Contracts\StoreMany;

class CacheRepositoryTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        Mockery::close();
    }

    public function testGetReturnsValueFromCache()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->getMany(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->get(['foo', 'baz']));

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->once()->with(['foo', 'baz'])->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->getMany(['foo', 'baz']));

        $repo->getStore()->shouldReceive('getMany')->once()->with(['foo', 'baz'])->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->get(['foo', 'baz']));
    }

    public function testDefaultValueIsReturned()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->getMany(['foo', 'baz'], 'bar'));
        $this->assertEquals(['foo' => 'bar', 'baz' => 'bar'], $repo->get(['foo', 'baz'], 'bar'));

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->with(['foo', 'baz'])->andReturn(['foo' => null, 'baz' => null]);
        $this->assertEquals(['foo' => null, 'baz' => null], $repo->getMany(['foo', 'baz']));
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

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => null, 'bar' => null]);
        $this->assertEquals(['foo' => false, 'bar' => false], $repo->has(['foo', 'bar']));

        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => 'baz', 'bar' => 'boom']);
        $this->assertEquals(['foo' => true, 'bar' => true], $repo->has(['foo', 'bar']));
    }

    public function testAddMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->addMany(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('get')->twice()->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->add(['foo', 'baz'], ['bar', 'boom'], 10);

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->add('foo', 'bar', 10);

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->addMany(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->add(['foo', 'baz'], ['bar', 'boom'], 10);
    }

    public function testPutMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->putMany(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $repo->put(['foo', 'baz'], ['bar', 'boom'], 10);

        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->put('foo', 'bar', 10);

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->putMany(['foo' => 'bar', 'baz' => 'boom'], 10);

        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $repo->put(['foo', 'baz'], ['bar', 'boom'], 10);
    }

    public function testForeverMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $repo->foreverMany(['foo' => 'bar', 'baz' => 'boom']);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $repo->forever(['foo', 'baz'], ['bar', 'boom']);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->forever('foo', 'bar');

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $repo->foreverMany(['foo' => 'bar', 'baz' => 'boom']);

        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $repo->forever(['foo', 'baz'], ['bar', 'boom']);
    }

    public function testPullMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->andReturn('boom');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('baz')->andReturn(true);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pullMany(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->andReturn('boom');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('baz')->andReturn(true);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pull(['foo', 'baz']));

        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $this->assertEquals('bar', $repo->pull('foo'));

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $repo->getStore()->shouldReceive('forgetMany')->once()->with(['foo', 'baz'])->andReturn(['foo' => true, 'baz' => true]);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pullMany(['foo', 'baz']));

        $repo->getStore()->shouldReceive('getMany')->once()->andReturn(['foo' => 'bar', 'baz' => 'boom']);
        $repo->getStore()->shouldReceive('forgetMany')->once()->with(['foo', 'baz'])->andReturn(['foo' => true, 'baz' => true]);
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $repo->pull(['foo', 'baz']));
    }

    public function testForgetMethod()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('bar')->andReturn(false);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forgetMany(['foo', 'bar']));

        $repo->getStore()->shouldReceive('forget')->once()->with('foo')->andReturn(true);
        $repo->getStore()->shouldReceive('forget')->once()->with('bar')->andReturn(false);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forget(['foo', 'bar']));

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('forgetMany')->once()->with(['foo', 'bar'])->andReturn(['foo' => true, 'bar' => false]);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forgetMany(['foo', 'bar']));

        $repo->getStore()->shouldReceive('forgetMany')->once()->with(['foo', 'bar'])->andReturn(['foo' => true, 'bar' => false]);
        $this->assertEquals(['foo' => true, 'bar' => false], $repo->forget(['foo', 'bar']));
    }

    public function testRememberMethodCallsPutAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $result = $repo->rememberMany(['foo', 'baz'], 10, function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $repo->getStore()->shouldReceive('put')->once()->with('baz', 'boom', 10);
        $result = $repo->remember(['foo', 'baz'], 10, function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('put')->once()->with('foo', 'bar', 10);
        $this->assertEquals('bar', $repo->remember('foo', 10, function () { return 'bar'; }));

        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $repo->getStore()->shouldNotReceive('put');
        $result = $repo->rememberMany(['foo', 'baz'], 10, function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $result = $repo->rememberMany(['foo', 'baz'], 10, function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('putMany')->once()->with(['foo' => 'bar', 'baz' => 'boom'], 10);
        $result = $repo->remember(['foo', 'baz'], 10, function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);
    }

    public function testRememberForeverMethodCallsForeverAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $result = $repo->rememberManyForever(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $result = $repo->rememberForever(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $this->assertEquals('bar', $repo->rememberForever('foo', function () { return 'bar'; }));

        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $repo->getStore()->shouldNotReceive('forever');
        $result = $repo->rememberManyForever(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $result = $repo->rememberManyForever(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $result = $repo->rememberForever(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);
    }

    public function testSearMethodCallsForeverAndReturnsDefault()
    {
        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->andReturn(null);
        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $result = $repo->searMany(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $repo->getStore()->shouldReceive('forever')->once()->with('baz', 'boom');
        $result = $repo->sear(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('forever')->once()->with('foo', 'bar');
        $this->assertEquals('bar', $repo->sear('foo', function () { return 'bar'; }));

        $repo = $this->getRepository();
        $repo->getStore()->shouldReceive('get')->once()->with('foo')->andReturn('bar');
        $repo->getStore()->shouldReceive('get')->once()->with('baz')->andReturn('boom');
        $repo->getStore()->shouldNotReceive('forever');
        $result = $repo->sear(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo = $this->getRepository(StoreMany::class);
        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $result = $repo->searMany(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);

        $repo->getStore()->shouldReceive('getMany')->andReturn(['foo' => null, 'baz' => null]);
        $repo->getStore()->shouldReceive('foreverMany')->once()->with(['foo' => 'bar', 'baz' => 'boom']);
        $result = $repo->sear(['foo', 'baz'], function () { return ['bar', 'boom']; });
        $this->assertEquals(['foo' => 'bar', 'baz' => 'boom'], $result);
    }

    protected function getRepository($contract = Store::class)
    {
        return new Repository(Mockery::mock($contract));
    }
}
