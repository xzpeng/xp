<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HostFile extends Model
{
    protected $table = 'host_file';
	protected $fillable = ['host_id', 'file_id', 'upload_path'];
}
