<?php

namespace App\Models;

use App\Helpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['model', 'action'];
	//protected $guarded = ['permissionable_type', 'permissionable_id'];
    protected $hidden = ['id', 'permissionable_type', 'permissionable_id'];

	public $timestamps = false;

	public function permissionable()
	{
		return $this->morphTo();
	}
}

