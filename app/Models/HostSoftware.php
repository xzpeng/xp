<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostSoftware extends Model
{
	protected $table = 'host_software';
	protected $fillable = ['host_id', 'software_id'];
}
