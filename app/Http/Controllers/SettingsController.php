<?php

namespace App\Http\Controllers;

use App\Models\MailSetting;
use App\Services\MailConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SettingsController extends Controller
{
    public function edit()
    {
        $mailSettings = MailSetting::instance();

        return view('settings.index', compact('mailSettings'));
    }

    public function updateMail(Request $request)
    {
        $mailSettings = MailSetting::instance();

        $validated = $request->validate([
            'host' => 'required|string|max:191',
            'port' => 'required|integer|min:1|max:65535',
            'encryption' => 'nullable|in:tls,ssl,none',
            'username' => 'nullable|string|max:191',
            'password' => 'nullable|string|max:191',
            'from_address' => 'required|email|max:191',
            'from_name' => 'required|string|max:191',
        ]);

        $encryption = $validated['encryption'] === 'none' ? null : ($validated['encryption'] ?? null);

        $mailSettings->fill([
            'host' => $validated['host'],
            'port' => $validated['port'],
            'encryption' => $encryption,
            'username' => $validated['username'] ?? null,
            'from_address' => $validated['from_address'],
            'from_name' => $validated['from_name'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $mailSettings->password = $validated['password'];
        }

        $mailSettings->save();

        MailConfigService::apply($mailSettings);

        return redirect()->route('settings.edit')->with('success', 'SMTP settings saved successfully.');
    }

    public function testMail(Request $request)
    {
        $validated = $request->validate([
            'test_email' => 'required|email|max:191',
        ]);

        $mailSettings = MailSetting::instance();

        if (! $mailSettings->isConfigured()) {
            return back()->withErrors(['test_email' => 'Please save SMTP settings before sending a test email.']);
        }

        MailConfigService::apply($mailSettings);

        try {
            Mail::raw('This is a test email from Anvica NMS. Your SMTP settings are working correctly.', function ($message) use ($validated, $mailSettings) {
                $message->to($validated['test_email'])
                    ->subject('Anvica NMS - SMTP Test Email')
                    ->from($mailSettings->from_address, $mailSettings->from_name);
            });
        } catch (\Throwable $e) {
            return back()->withErrors(['test_email' => 'Failed to send test email: ' . $e->getMessage()]);
        }

        return redirect()->route('settings.edit')->with('success', 'Test email sent successfully to ' . $validated['test_email'] . '.');
    }
}
