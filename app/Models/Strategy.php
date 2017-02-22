<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Strategy extends Model
{
    /**
     * [host description]
     * @return [type] [description]
     */
    public function host()
    {
    	return $this->belongTo('App\Models\Host');
    }
}
