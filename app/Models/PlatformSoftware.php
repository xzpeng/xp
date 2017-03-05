<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSoftware extends Model
{
	protected $table = 'platform_software';
	protected $fillable = ['platform_id', 'software_id'];
}
