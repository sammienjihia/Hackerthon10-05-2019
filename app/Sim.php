<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sim extends Model
{
    //
    //
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'iccid', 'imsi', 'pin1', 'puc', 'ki'
    ];
}
