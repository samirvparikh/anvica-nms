<?php

namespace App\Services;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    public static function apply(?MailSetting $settings = null): void
    {
        if (! Schema::hasTable('mail_settings')) {
            return;
        }

        $settings ??= MailSetting::first();

        if (! $settings?->isConfigured()) {
            return;
        }

        $smtpConfig = array_merge(config('mail.mailers.smtp', []), [
            'transport' => 'smtp',
            'host' => $settings->host,
            'port' => $settings->port,
            'username' => $settings->username,
            'password' => $settings->password,
            'scheme' => $settings->encryption === 'ssl' ? 'smtps' : null,
        ]);

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => $smtpConfig,
            'mail.from.address' => $settings->from_address,
            'mail.from.name' => $settings->from_name ?? config('app.name'),
        ]);
    }
}
