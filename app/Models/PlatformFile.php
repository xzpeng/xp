<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformFile extends Model
{
    protected $table = 'platform_file';
	protected $fillable = ['platform_id', 'file_id', 'upload_path'];
}
