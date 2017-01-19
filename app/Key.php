<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    protected $dates = ['created_at', 'updated_at', 'last_run'];

    public function devices() {
        return $this->hasMany('App\Device');
    }

}
