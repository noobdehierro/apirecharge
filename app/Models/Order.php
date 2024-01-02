<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
        'sales_type',
        'offering_id',
        'msisdn',
        'email',
        'payment_method',
        'payment_id',
        'reference_id',
        'offering_name',
        'amount',
        'me_reference_id',
        'payment_request_id',
    ];
}
