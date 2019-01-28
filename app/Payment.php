<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model 
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'amount', 'payment_id', 'paid', 'card_id', 'name'
    ];
    public function user()
    {
        return $this->belongsTo('App\User', 'userId');
    }
}