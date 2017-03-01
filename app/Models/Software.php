<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Software extends Model
{
    public function hosts()
    {
    	return $this->belongsToMany('App\Models\Software');
    }
}
