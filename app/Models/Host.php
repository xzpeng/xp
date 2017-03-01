<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Host extends Model
{
    /**
     * Get strategies
     * @return [type] [description]
     */
    public function strategies()
    {
    	return $this->hasMany('App\Models\Strategy');
    }

    public function softwares()
    {
        return $this->belongsToMany('App\Models\Software');
    }


    public static function options($id)
    {
        if (! $self = static::where('status', 1)->find($id)) {
            return self::where('status', 1)->pluck('name', 'id');
        }

        return $self->brothers()->pluck('name', 'id');
    }
}
