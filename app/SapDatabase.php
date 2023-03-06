<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SapDatabase extends Model
{
    protected $fillable = ['server', 'database', 'username', 'password'];
}
