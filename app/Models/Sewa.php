<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sewa extends Model
{
    use SoftDeletes;

    protected $table = 'kmg_sewas';

    protected $primaryKey = 'kmg_id';

    protected $guarded = ['kmg_id'];

    protected $casts = [
        'kmg_check_in' => 'datetime',
    ];
}
