<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = ['label', 'code', 'value', 'group', 'is_protected'];

    public $sortable = ['id', 'label', 'code', 'value', 'group', 'is_protected'];
}
