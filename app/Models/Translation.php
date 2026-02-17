<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
 protected $fillable = [
        'table_name',
        'record_id',
        'field_name',
        'language_code',
        'value',
    ];
}