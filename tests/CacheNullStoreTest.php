<?php

use PulkitJalan\Cache\NullStore;

class CacheNullStoreTest extends PHPUnit_Framework_TestCase
{
    public function testItemsCanNotBeCached()
    {
        $store = new NullStore;
        $store->put(['foo', 'baz'], ['bar', 'boom'], 10);
        $this->assertEquals(['foo' => null, 'baz' => null], $store->get(['foo', 'baz']));
    }
}