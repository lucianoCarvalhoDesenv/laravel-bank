<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function scopeOwner($query, $ownerid)
    {
        if (!is_null($ownerid)) {
            return $query->where('owner', '=', $ownerid);
        }

        return $query;
    }

    public function scopeApproved($query, $approved)
    {
        if (!is_null($approved)) {
            return $query->where('approved', '=', $approved);
        }

        return $query;
    }

    public function scopeType($query, $type)
    {
        if (!is_null($type)) {
            return $query->where('type', '=', $type);
        }

        return $query;
    }
}
