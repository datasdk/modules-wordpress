<?php

namespace Modules\Wordpress\Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Wordpress\Services\CreateOrderService;
use Modules\Wordpress\Services\UpdateOrderService;
use Modules\Wordpress\Services\DeleteOrderService;
use Modules\Memberships\Models\Plan;



class WordpressApiTest extends TestCase
{
    use RefreshDatabase;



    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->createUser([
            "email" => "info@example.com",
        ]);

    }


    public function test_create_order()
    {

        $payload = [
            "order_id" => 1,
            "action" => "created",
            "customer" => [
                "billing" => [
                    "first_name" => "Jhone",
                    "last_name" => "Doe",
                    "email" => "info@example.com",
                    "phone" => "12345678910"
                ]
            ],
            "products" => [
                [
                    "id" => 1,
                    "name" => "product name",
                    "sku" => "0001"
                ]
            ]
        ];


        $expectedResponse = [
            'orders' => ['subscription_id_123'],
            'customer' => [
                'id' => 1,
                'email' => 'info@example.com',
            ],
        ];


   
        $response = $this->actingAs($this->user)->postJson(route('api.wordpress.order.create'), $payload);


        $response->assertStatus(200);


    }

    public function testUpdateOrder()
    {

   
        $payload = [
            'order_id' => 1,
            'action' => 'updated',
            'customer' => [
                "billing" => [
                    "first_name" => "Jhone",
                    "last_name" => "Doe",
                    "email" => "info@example.com",
                    "phone" => "12345678910"
                ]
            ],
            'products' => [
                [
                    'id' => 1,
                    'name' => "product name",
                    'sku' => '0001'
                ]
            ]
        ];

        $orderId = $payload['order_id'];

        $expectedResponse = [
            'updated' => true,
            'order_id' => $orderId,
        ];

 

        $response = $this->actingAs($this->user)->patchJson(route('api.wordpress.order.update'), $payload);

        $response->assertStatus(200);
  
    }


    public function testDeleteOrder()
    {
        $payload = [
            'order_id' => 1,
            'action' => 'deleted',
            'customer' => [
                "billing" => [
                    "first_name" => "Jhone",
                    "last_name" => "Doe",
                    "email" => "info@example.com",
                    "phone" => "12345678910"
                ]
            ]
        ];

        $orderId = $payload['order_id'];



        $response = $this->actingAs($this->user)->deleteJson(route('api.wordpress.order.destroy'), $payload);

        $response->assertNoContent();
     

    }

}
