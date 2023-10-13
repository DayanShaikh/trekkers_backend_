<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class UserDropboxLink extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'trip_id', 'status','creater_id'];
}
