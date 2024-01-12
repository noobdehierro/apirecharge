<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IgClientsStorePay extends Model
{
    use HasFactory;
    
    public $timestamps = false;

    protected $connection = 'second_mysql';

    protected $table = 'igclients_store_pay';

    protected $fillable = [
        'action',
        'plan',
        'price',
        'productId',
        'msisdn',
        'orderId',
        'referencia',
        'type',
        'koonolEmail',
        'creation_date',
    ];
}
