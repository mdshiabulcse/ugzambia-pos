<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Import extends Model
{
    use HasFactory;

    protected $fillable = [
        'province',
        'district',
        'ministry',
        'employeeNo',
        'manNo',
        'nrcNo',
        'names',
        'referenceNo',
        'periodName',
        'subTotal',
        'total',
        'empNo',
        'recNo',
        'importAt',
        'importBy',
    ];

    public $timestamps = false;
}
