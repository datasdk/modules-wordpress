<?php

namespace Modules\Wordpress\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust according to your authorization needs
    }

    public function rules()
    {
        return [
            'order_id' => 'required|integer',
            'action' => 'required|string|in:created,updated,deleted',
            'customer.billing.first_name' => 'required',
            'customer.billing.last_name' => 'sometimes',
            'customer.billing.address' => 'sometimes',
            'customer.billing.address_2' => 'sometimes|nullable',
            'customer.billing.postcode' => 'sometimes|string|max:10',
            'customer.billing.city' => 'sometimes',
            'customer.billing.email' => 'required|email',
            'customer.billing.phone' => 'sometimes',
            'customer.billing.country' => 'sometimes',
            'customer.billing.state' => 'sometimes|nullable',
            'customer.billing.company' => 'sometimes|nullable',
            'customer.billing.vat' => 'sometimes|nullable',
            'customer.billing.note' => 'sometimes|nullable',
            'customer.billing.payment_method' => 'sometimes|nullable',
            'customer.shipping.first_name' => 'sometimes',
            'customer.shipping.last_name' => 'sometimes',
            'customer.shipping.address' => 'sometimes',
            'customer.shipping.address_2' => 'sometimes|nullable',
            'customer.shipping.postcode' => 'sometimes',
            'customer.shipping.city' => 'sometimes',
            'customer.shipping.phone' => 'sometimes',
            'customer.shipping.country' => 'sometimes',
            'customer.shipping.state' => 'sometimes|nullable',
            'customer.shipping.company' => 'sometimes|nullable',
            'customer.shipping.vat' => 'sometimes|nullable',
            'products' => 'sometimes|array',
            'products.*.id' => 'sometimes|integer',
            'products.*.product_id' => 'sometimes|integer',
            'products.*.sku' => 'sometimes',
            'products.*.name' => 'sometimes',
            'products.*.description' => 'sometimes|nullable',
            'products.*.quantity' => 'sometimes|integer',
            'products.*.total' => 'sometimes|numeric',
            'products.*.tax' => 'sometimes|numeric',
            'products.*.shipping' => 'sometimes|numeric',
            'products.*.virtual' => 'sometimes|boolean',
            'products.*.stock' => 'sometimes|integer',
            'products.*.stock_status' => 'sometimes|string',
            'products.*.regular_price' => 'sometimes|numeric',
            'products.*.sale_price' => 'sometimes|numeric',
            'products.*.subtotal' => 'sometimes|numeric',
            'products.*.variation_id' => 'sometimes|nullable|integer',
            'products.*.attributes' => 'sometimes|nullable|array',
            'products.*.downloadable' => 'sometimes|boolean',
            'products.*.created_at' => 'sometimes|date',
            'products.*.visibility' => 'sometimes|string',
            'products.*.product_url' => 'sometimes|url',
            'products.*.images' => 'sometimes|nullable|array',
            'products.*.categories' => 'sometimes|nullable|array',
            'products.*.meta_data' => 'sometimes|nullable|array',
        ];
    }

    public function messages()
    {
        return [
            'order_id.required' => 'Order ID is required for update.',
            'action.required' => 'Action is required.',
            'customer.billing.first_name.required' => 'Billing first name is required.',
            // Add more custom messages for each rule
        ];
    }
}
