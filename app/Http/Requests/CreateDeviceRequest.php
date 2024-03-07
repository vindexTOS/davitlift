<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateDeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules()
    {
        return [
            'can_search' => 'required|boolean',
            'limit' => 'required|integer|min:0',
            'lastBeat' => 'nullable|date',
            'fixed_amount' => 'nullable|numeric|between:0,999999.99',
            'relay_pulse_time' => 'nullable|integer|min:0|max:255',
            'lcd_brightness' => 'nullable|integer|min:0|max:100',
            'led_brightness' => 'nullable|integer|min:0|max:100',
            'msg_appear_time' => 'nullable|integer|min:0|max:255',
            'card_read_delay' => 'nullable|integer|min:0|max:255',
            'tariff_amount' => 'nullable|integer|min:0|max:9900',
            'timezone' => 'nullable|integer|min:-128|max:127',
            'storage_disable' => 'required|boolean',
            'relay1_node' => 'required',
            'dev_name' => 'nullable|string|max:6',
            'op_mode' => 'nullable|integer|max:255',
            'dev_id' => 'required|string|max:255',
            'guest_msg_L1' => 'nullable|string|max:15',
            'guest_msg_L2' => 'nullable|string|max:15',
            'guest_msg_L3' => 'nullable|string|max:15',
            'validity_msg_L1' => 'nullable|string|max:15',
            'validity_msg_L2' => 'nullable|string|max:15',
            'name' => 'required|string|max:255',
            'comment' => 'nullable|string',
            'company_id' => 'required|integer|min:0',
            'admin_email' => 'required'
        ];
    }
}
