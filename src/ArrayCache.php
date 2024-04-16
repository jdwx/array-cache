<?php


declare( strict_types = 1 );


namespace JDWX\ArrayCache;


use DateInterval;
use JsonSerializable;
use Psr\SimpleCache\CacheInterface;


/** A trivial implementation of CacheInterface using an in-memory array. */
class ArrayCache implements CacheInterface, JsonSerializable {


    /** @var array<string, ArrayCacheItem> The cache data. */
    protected array $data = [];


    /**
     * The cache can be preloaded with either a JSON string (e.g., one previously
     * saved with json_encode) or an array of cache items.  If the array is used,
     * its items may either be the cached values (which will be cached indefinitely)
     * or arrays of the form [ 'data' => <value>, 'expires': <expiration time> ].
     */
    public function __construct( array|string|null $i_preload = null ) {
        if ( is_string ( $i_preload ) ) {
            $i_preload = json_decode( $i_preload, true, 3, JSON_THROW_ON_ERROR );
        }
        if ( is_array( $i_preload ) ) {
            foreach ( $i_preload as $key => $item ) {
                $item = ArrayCacheItem::newFromLoad( $item );
                if ( ! $item->isExpired() ) {
                    $this->data[ $key ] = $item;
                }
            }
        }
    }


    /** @inheritdoc */
    public function clear() : bool {
        $this->data = [];
        return true;
    }


    /** @inheritdoc */
    public function delete( string $key ) : bool {
        unset( $this->data[ $key ] );
        return true;
    }


    /** @inheritdoc */
    public function deleteMultiple( iterable $keys ) : bool {
        $bOK = true;
        foreach ( $keys as $key ) {
            $bOK = $this->delete( $key ) && $bOK;
        }
        return $bOK;
    }


    /** @inheritdoc */
    public function get( string $key, mixed $default = null ) : mixed {
        if ( ! $this->has( $key ) ) {
            return $default;
        }
        return $this->data[ $key ]->get( $default );
    }


    /** @inheritdoc */
    public function getMultiple( iterable $keys, mixed $default = null ) : iterable {
        $rOut = [];
        foreach ( $keys as $key ) {
            $rOut[ $key ] = $this->get( $key, $default );
        }
        return $rOut;
    }


    /** @inheritdoc */
    public function has( string $key ) : bool {
        if ( ! isset( $this->data[ $key ] ) ) {
            return false;
        }
        if ( $this->data[ $key ]->isExpired() ) {
            $this->delete( $key );
            return false;
        }
        return true;
    }


    /** @inheritdoc */
    public function jsonSerialize() : array {
        $rOut = [];
        foreach ( $this->data as $key => $item ) {
            if ( ! $item->isExpired() ) {
                $rOut[ $key ] = $item->jsonSerialize();
            }
        }
        return $rOut;
    }


    /** @inheritdoc */
    public function set( string $key, mixed $value, DateInterval|int|null $ttl = null ) : bool {
        $this->data[ $key ] = new ArrayCacheItem( $value, ArrayCacheItem::expireTime( $ttl ) );
        return true;
    }


    /** @inheritdoc */
    public function setMultiple( iterable $values, DateInterval|int|null $ttl = null ) : bool {
        $bOK = true;
        foreach ( $values as $key => $value ) {
            $bOK = $this->set( $key, $value, $ttl ) && $bOK;
        }
        return $bOK;
    }


}
