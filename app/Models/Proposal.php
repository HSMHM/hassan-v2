<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'proposal_id',
        'customer_name',
        'description',
        'password',
        'content',
        'locale',
        'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'content' => 'array',
        'is_active' => 'boolean',
    ];
}
