<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $deviceId = $this->route('device')?->id;

        return [
            'name' => 'required|string|max:191|unique:devices,name,' . $deviceId,
            'service_id' => 'required|exists:services,id',
            'vendor_id' => 'nullable|exists:device_vendors,id',
            'hostname' => 'nullable|string|max:191',
            'type' => 'required|string|max:191',
            'ip_address' => 'required|ip',
            'location' => 'required|string|max:191',
            'api_url' => 'nullable|url|max:500',
            'api_username' => 'nullable|string|max:191',
            'api_password' => 'nullable|string|max:191',
            'snmp_version' => 'nullable|in:1,2c,3',
            'snmp_port' => 'nullable|integer|min:1|max:65535',
            'snmp_community' => 'nullable|string|max:191',
            'status' => 'required|in:Up,Warning,Down',
            'user_id' => 'nullable|exists:users,id',
        ];
    }
}
