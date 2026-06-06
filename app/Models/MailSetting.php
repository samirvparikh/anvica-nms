<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }

    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'port' => 587,
            'encryption' => 'tls',
        ]);
    }

    public function isConfigured(): bool
    {
        return filled($this->host) && filled($this->from_address);
    }
}
