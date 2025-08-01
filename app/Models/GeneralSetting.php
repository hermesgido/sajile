<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $casts = ['mail_config' => 'object',
    'sms_config' => 'object',
    'global_shortcodes' => 'object',
    'pusher_credential' => 'object',
    'socialite_credentials' => 'object',
    
];

    public function scopeSiteName($query, $pageTitle)
    {
        $pageTitle = empty($pageTitle) ? '' : $pageTitle . ' - ';
        return $pageTitle . $this->site_name;
    }

    protected static function boot()
    {
        parent::boot();
        static::saved(function(){
            \Cache::forget('GeneralSetting');
        });
    }
}
