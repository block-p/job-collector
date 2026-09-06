<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobQueue extends Model
{
    protected $table = 'jobs';
    public $timestamps = false;
    protected $guarded = [];
}
