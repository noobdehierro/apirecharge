<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IgOrdersRecharge extends Model
{
    use HasFactory;
    public $timestamps = false;

    protected $connection = 'second_mysql';

    protected $table = 'igorders_recharge';

    protected $fillable = [
        'action',
        'plan',
        'price',
        'product_id',
        'msisdn',
        'koonolEmail',
        'orderId',
        'conekta_order_id',
        'referencia_conekta',
        'response',
        'estatus',
        'creation_date',
    ];
}
