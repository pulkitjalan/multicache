<?php

use PulkitJalan\Cache\ArrayStore;

class CacheArrayStoreTest extends PHPUnit_Framework_TestCase
{
    public function testItemsCanBeSetAndRetrieved()
    {
        $store = new ArrayStore;
        $store->putMulti(['foo' => 'bar', 'baz' => 'boom'], 10);
        $this->assertEquals('bar', $store->get('foo'));
        $this->assertEquals('boom', $store->get('baz'));
    }
}
