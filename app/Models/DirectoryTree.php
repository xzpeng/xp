<?php

namespace App\Models;

use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class DirectoryTree extends Model
{
    use ModelTree,AdminBuilder;

    protected $table = 'directory_trees';
    protected $fillable = ['parent_id', 'platform_id', 'order', 'name','name_relative', 'file_type'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setParentColumn('parent_id');
        $this->setOrderColumn('order');
        $this->setTitleColumn('name');
    }
}
