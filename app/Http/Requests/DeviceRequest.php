<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'startup'            => 'nullable|integer|min:0|max:65535',
            'relay_pulse_time'   => 'nullable|integer|min:0|max:255',
            'lcd_brightness'     => 'nullable|integer|min:0|max:255',
            'led_brightness'     => 'nullable|integer|min:0|max:255',
            'msg_appear_time'    => 'nullable|integer|min:0|max:255',
            'card_read_delay'    => 'nullable|integer|min:0|max:255',
            'tariff_amount'      => 'nullable|integer|min:0|max:255',
            'timezone'           => 'nullable|integer|min:-128|max:127',
            'storage_disable'    => 'nullable|boolean',
            'relay1_node'        => 'nullable|boolean',
            'dev_name'           => 'nullable|string|max:7',
            'op_mode'            => 'nullable|string|max:255',
            'dev_id'             => 'nullable|string|max:7',
            'guest_msg_L1'       => 'nullable|string|max:16',
            'guest_msg_L2'       => 'nullable|string|max:16',
            'guest_msg_L3'       => 'nullable|string|max:16',
            'validity_msg_L1'    => 'nullable|string|max:16',
            'validity_msg_L2'    => 'nullable|string|max:16',
            'name'               => 'nullable|string|max:255',
            'comment'            => 'nullable|string',
            'company_id'         => 'nullable|integer|min:0',
            'users_id'           => 'nullable|integer|min:0',
            'soft_version'       => 'nullable|string|max:255',
            'hardware_version'   => 'nullable|string|max:255',
            'url'                => 'nullable|string|max:255',
            'crc32'              => 'nullable|string|max:255',
        ];
    }
}
