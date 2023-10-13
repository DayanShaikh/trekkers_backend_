<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserField extends Model
{
	protected $fillable = ['field_key', 'field_value'];
    use HasFactory;
}
