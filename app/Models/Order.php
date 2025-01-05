<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model {
    use SoftDeletes;

    protected $fillable = [ 'location_id', 'product_id', 'quantity', 'rate', 'comments' ];

    protected $dates = [ 'deleted_at' ];

    protected $casts = [
        'quantity' => 'integer',
        'rate' => 'integer'
    ];

    public function product() {
        return $this->hasOne( Product::class );
    }

    public function location() {
        return $this->belongsTo( Location::class );
    }

    public function invoice() {
        return $this->belongsTo( Invoice::class );
    }

    public function scopeCurrentOrders( $query, int $location_id = 0 ) {
        if ( $location_id > 0 ) {
            $query->where( 'location_id', $location_id );
        }

        return $query->whereIn( 'status', [ 'OPEN', 'ACCEPTED', 'COMPLETED' ] );
    }

    /**
    * Returns Open Order
    * if Location Id is provided Open orders for Location
    */

    public function scopeOpen( $query, int $location_id = 0 ) {
        if ( $location_id > 0 ) {
            $query->where( 'location_id', $location_id );
        }

        return $query->whereIn( 'status', [ 'OPEN', 'ACCEPTED' ] );
    }

    /**
    * Returns all Completed Order
    */

    public function scopeCompleted( $query, int $location_id = 0 ) {
        if ( $location_id > 0 ) {
            $query->where( 'location_id', $location_id );
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

    public function paid( int $invoice_id ) {
        if ( $this->status == 'COMPLETED' ) {
            $this->status == 'PAID';
            $this->invoice_id = $invoice_id;
            return $this->save();
        }
        return false;
    }

    public function getAmountAttribute() {
        return $this->quantity * $this->rate;
    }
}