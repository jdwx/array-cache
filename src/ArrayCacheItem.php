<?php


declare( strict_types = 1 );


namespace JDWX\ArrayCache;


use DateInterval;
use JsonSerializable;


/** Holds one cached item with optional expiration time. */
class ArrayCacheItem implements JsonSerializable {


    private mixed $data;

    private ?float $expires;


    /** Construct a cached item. */
    public function __construct( mixed $i_data, ?float $i_fExpires = null ) {
        $this->data = $i_data;
        $this->expires = $i_fExpires;
    }


    public static function expireTime( DateInterval|int|null $i_ttl ) : ?float {
        if ( $i_ttl instanceof DateInterval ) {
            return microtime( true ) + $i_ttl->s;
        } elseif ( is_int( $i_ttl ) ) {
            return microtime( true ) + $i_ttl;
        }
        return null;
    }


    /** Returns the cached item's data or the default value if expired. */
    public function get( mixed $i_default = null ) : mixed {
        if ( $this->isExpired() ) {
            return $i_default;
        }
        return $this->data;
    }


    public function isExpired() : bool {
        if ( ! is_float( $this->expires ) ) {
            return false;
        }
        return $this->expires < microtime( true );
    }


    public function jsonSerialize() : array {
        return [
            'data' => $this->data,
            'expires' => $this->expires,
        ];
    }


    public static function newFromLoad( mixed $i_xContent ) : ArrayCacheItem {
        if ( is_array( $i_xContent ) && isset( $i_xContent[ 'data' ] ) && array_key_exists( 'expires', $i_xContent ) ) {
            return new ArrayCacheItem( $i_xContent[ 'data' ], $i_xContent[ 'expires' ] );
        }
        return new ArrayCacheItem( $i_xContent, null );
    }


}
