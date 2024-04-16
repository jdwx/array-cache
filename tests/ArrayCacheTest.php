<?php


declare( strict_types = 1 );


use JDWX\ArrayCache\ArrayCache;
use PHPUnit\Framework\TestCase;


/**
 * Tests for the ArrayCacheBackend class.
 */
class ArrayCacheTest extends TestCase {


    public function testClear() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        static::assertTrue( $cache->has( 'foo' ) );
        $cache->clear();
        static::assertFalse( $cache->has( 'foo' ) );
    }


    public function testDelete() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        static::assertTrue( $cache->has( 'foo' ) );
        $cache->delete( 'foo' );
        static::assertFalse( $cache->has( 'foo' ) );
    }


    public function testDeleteMultiple() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        $cache->set( 'baz', 'qux' );
        $cache->set( 'quux', 'corge' );
        static::assertTrue( $cache->has( 'foo' ) );
        static::assertTrue( $cache->has( 'baz' ) );
        static::assertTrue( $cache->has( 'quux' ) );
        $cache->deleteMultiple( [ 'foo', 'baz' ] );
        static::assertFalse( $cache->has( 'foo' ) );
        static::assertFalse( $cache->has( 'baz' ) );
        static::assertTrue( $cache->has( 'quux' ) );
    }


    public function testGet() : void {
        $cache = new ArrayCache();
        static::assertNull( $cache->get( 'foo' ) );
        $cache->set( 'foo', 'bar' );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
    }


    public function testGetMultiple() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        $cache->set( 'baz', 'qux' );
        $cache->set( 'quux', 'corge' );
        $rOut = $cache->getMultiple( [ 'foo', 'baz', 'quux' ] );
        static::assertSame( 'bar', $rOut[ 'foo' ] );
        static::assertSame( 'qux', $rOut[ 'baz' ] );
        static::assertSame( 'corge', $rOut[ 'quux' ] );
    }


    public function testHas() : void {
        $cache = new ArrayCache();
        static::assertFalse( $cache->has( 'foo' ) );
        $cache->set( 'foo', 'bar' );
        static::assertTrue( $cache->has( 'foo' ) );
    }


    public function testHasForExpired() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar', -1 );
        static::assertFalse( $cache->has( 'foo' ) );
    }


    public function testSet() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
        $cache->set( 'foo', 'baz' );
        static::assertSame( 'baz', $cache->get( 'foo' ) );
    }


    public function testSetMultiple() : void {
        $cache = new ArrayCache();
        $cache->setMultiple( [ 'foo' => 'bar', 'baz' => 'qux', 'quux' => 'corge' ] );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
        static::assertSame( 'qux', $cache->get( 'baz' ) );
        static::assertSame( 'corge', $cache->get( 'quux' ) );
    }


    public function testConstructFromRawData() : void {
        $cache = new ArrayCache( [ 'foo' => 'bar', 'baz' => 'qux' ] );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
        static::assertSame( 'qux', $cache->get( 'baz' ) );
    }


    public function testConstructFromSerializedData() : void {
        $cache = new ArrayCache( [
            'foo' => [ 'data' => 'bar', 'expires' => null ],
            'baz' => [ 'data' => 'qux', 'expires' => microtime( true ) + 1_000 ],
            'quux' => [ 'data' => 'corge', 'expires' => microtime( true ) - 1_000 ],
        ] );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
        static::assertSame( 'qux', $cache->get( 'baz' ) );
        static::assertNull( $cache->get( 'quux' ) );
    }


    public function testConstructFromString() : void {
        $cache = new ArrayCache( json_encode( [
            'foo' => [ 'data' => 'bar', 'expires' => null ],
            'baz' => [ 'data' => 'qux', 'expires' => microtime( true ) + 1_000 ],
            'quux' => [ 'data' => 'corge', 'expires' => microtime( true ) - 1_000 ],
        ] ) );
        static::assertSame( 'bar', $cache->get( 'foo' ) );
        static::assertSame( 'qux', $cache->get( 'baz' ) );
        static::assertNull( $cache->get( 'quux' ) );
    }


    public function testJsonSerialize() : void {
        $cache = new ArrayCache();
        $cache->set( 'foo', 'bar' );
        $cache->set( 'baz', 'qux', 1000 );
        $cache->set( 'quux', 'corge' );
        $json = json_encode( $cache );
        $rOut = json_decode( $json, true );
        static::assertSame( 'bar', $rOut[ 'foo' ][ 'data' ] );
        static::assertNull( $rOut[ 'foo' ][ 'expires' ] );
        static::assertSame( 'qux', $rOut[ 'baz' ][ 'data' ] );
        static::assertGreaterThan( microtime(true), $rOut[ 'baz' ][ 'expires' ] );
        static::assertSame( 'corge', $rOut[ 'quux' ][ 'data' ] );
        static::assertNull( $rOut[ 'quux' ][ 'expires' ] );
    }


}
