<?php

namespace Modules\Wordpress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust according to your authorization needs
    }

    public function rules()
    {
        return [
            'order_id' => 'required|integer',  // Ensure the order exists before deletion
            'customer.billing.email' => 'required|email',
            'products.*.sku' => 'sometimes',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => 'Order ID is required to delete.',
            'order_id.exists' => 'The order you are trying to delete does not exist.',
        ];
    }
}
