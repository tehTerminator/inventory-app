<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {
    use SoftDeletes;

    protected $fillable = [ 'location_id', 'product_id', 'quantity', 'rate' ];
    protected $dates = [ 'deleted_at' ];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'integer'
    ];

    protected static function booted() {
        static::addGlobalScope( 'withProduct', function ( $query ) {
            $query->with( 'product' );
        });
    }

    public function product() {
        return $this->hasOne( Product::class );
    }

    public function location() {
        return $this->belongsTo( Location::class );
    }

    public function invoice() {
        return $this->belongsTo( Invoice::class );
    }

    /**
    * Returns Open Order
    * if Location Id is provided Open orders for Location
    */

    public function scopeOpen( $query, int $location_id = 0 ) {
        if ( $location_id == 0 ) {
            return $query->whereIn( 'status', [ 'OPEN', 'ACCEPTED' ] );
        }

        return $query->whereIn( 'status', [ 'OPEN', 'ACCEPTED' ] )->andWhere( 'location_id', $location_id );
    }

    /**
    * Returns all Completed Order
    */

    public function scopeCompleted( $query, int $location_id = 0 ) {
        if ( $location_id > 0 ) {
            return $query->where( 'location_id', $location_id )->andWhere( 'status', 'COMPLETED' );
        }
        return $query->where( 'status', 'COMPLETED' );
    }

    /***
    * Changes Status From Open to Accepted
    */

    public function accept() {
        if ( $this->status === 'OPEN' ) {
            $this->status = 'ACCEPTED';
            return $this->save();
        }
        return false;
    }

    /**
    * Changes Status From Open or Accepted to Cancelled
    * @return false if Status is not Open or Accepted
    * @return order Returns the updated Order if success
    */

    public function cancel() {
        if ( $this->status == 'OPEN' || $this->status == 'ACCEPTED' ) {
            $this->status = 'CANCELLED';
            return $this->save();
        }
        return false;
    }

    /**
    * Changes Status From Accepted to COMPLETED
    * @return false if Status is not Accepted
    * @return order Returns the updated Order if success
    */

    public function complete() {
        if ( $this->status === 'OPEN' || $this->status == 'ACCEPTED' ) {
            $this->status == 'COMPLETED';
            return $this->save();
        }
        return false;
    }

    /**
    * Changes Status From COMPLETED to PAID
    * @return false if Status is not COMPLETED
    * @return order Returns the updated Order if success
    */

    public function paid() {
        if ( $this->status == 'COMPLETED' ) {
            $this->status == 'PAID';
            return $this->save();
        }
        return false;
    }

    public function getAmountAttribute() {
        return $this->quantity * $this->rate;
    }
}