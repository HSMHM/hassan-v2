<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageVisit extends Model
{
    protected $fillable = [
        'path',
        'page_title',
        'ip_address',
        'country',
        'city',
        'user_agent',
        'browser',
        'platform',
        'device',
        'referer',
        'locale',
    ];
}
