<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Platform extends Model
{
    protected $table = 'platform_main';
    protected $fillable = ['platform_name', 'platform_ip', 'platform_sn', 'alive', 'platform_root', 'platform_rootpwd', 'install_status', 'securitysoft_id'];
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
