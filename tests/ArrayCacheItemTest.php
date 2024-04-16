<?php


declare( strict_types = 1 );


use JDWX\ArrayCache\ArrayCacheItem;
use PHPUnit\Framework\TestCase;


/**
 *  Tests for the ArrayCacheItem class.
 */
class ArrayCacheItemTest extends TestCase {


    public function testGet() : void {
        $item = new ArrayCacheItem( 'foo', null );
        static::assertSame( 'foo', $item->get() );
    }


    public function testGetForNotUsingDefault() : void {
        $item = new ArrayCacheItem( 'foo', null );
        static::assertSame( 'foo', $item->get( 'bar' ) );
    }


    public function testGetForUsingDefault() : void {
        $item = new ArrayCacheItem( 'foo', ArrayCacheItem::expireTime( 1 ) );
        static::assertSame( 'foo', $item->get( 'baz' ) );
        $item = new ArrayCacheItem( 'foo', ArrayCacheItem::expireTime( -1 ) );
        static::assertSame( 'baz', $item->get( 'baz' ) );
    }


    public function testIsExpiredForDoesntExpire() : void {
        $item = new ArrayCacheItem( 'foo', null );
        static::assertFalse( $item->isExpired() );
    }


    public function testIsExpiredForNotExpired() : void {
        $item = new ArrayCacheItem( 'foo', ArrayCacheItem::expireTime( 1_000 ) );
        static::assertFalse( $item->isExpired() );
    }


    public function testIsExpiredForExpired() : void {
        $item = new ArrayCacheItem( 'foo', ArrayCacheItem::expireTime( -1 ) );
        static::assertTrue( $item->isExpired() );
    }


    public function testIsExpiredForExpiredWithDateInterval() : void {
        $expires = ArrayCacheItem::expireTime( new DateInterval( 'PT1S' ) );
        self::assertEqualsWithDelta( microtime( true ) + 1, $expires, 0.01 );
    }


    public function testJsonSerialize() : void {
        $item = new ArrayCacheItem( 'foo', null );
        static::assertSame( '{"data":"foo","expires":null}', json_encode( $item ) );
    }


    public function testNewFromLoadForSerialized() : void {
        $item = new ArrayCacheItem( 'foo', null );
        $stJSON = json_encode( $item );
        $rJSON = json_decode( $stJSON, true );
        $item = ArrayCacheItem::newFromLoad( $rJSON );
        static::assertSame( 'foo', $item->get() );
        static::assertFalse( $item->isExpired() );
    }


    public function testNewFromLoadForSimpleData() : void {
        $item = ArrayCacheItem::newFromLoad( 'foo' );
        static::assertSame( 'foo', $item->get() );
        static::assertFalse( $item->isExpired() );
    }


}
