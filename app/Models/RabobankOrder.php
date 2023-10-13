<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class RabobankOrder extends Model
{
    protected $fillable = ['type', 'amount', 'redirect'];

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }
}
