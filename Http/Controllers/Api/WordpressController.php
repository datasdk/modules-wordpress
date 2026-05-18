<?php

namespace Modules\Wordpress\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use Modules\Memberships\Models\Plan;
use Modules\Shop\Events\ShopOrderCreate;
use Webpatser\Countries\Countries;
use Illuminate\Support\Facades\Log;
use Modules\Crm\Services\UserService;
use Throwable;

class WordpressController
{


    /**
     * Opret en ordre
     */
    public function store(Request $req)
    {
        try {
            $orderData = $req->all();

            $customer = $this->createOrGetCustomer($orderData['customer'] ?? []);

            if (!$customer) {
                return response()->json(['error' => 'Customer creation failed'], 400);
            }

            $orders = $this->handleOrderProducts($customer, $orderData['products'] ?? []);

            event(new ShopOrderCreate($customer, $orderData));

            return [
                'customer' => $customer,
                'orders' => $orders,
            ];
        } catch (Throwable $ex) {
            Log::error($ex->getMessage());
            return $this->errorResponse($ex);
        }
    }


    /**
     * Opdater en ordre
     */
    public function update(Request $req)
    {


        $orderData = $req->all();
        $orderId = $req->input('order_id');

        try {
            $customer = $this->updateCustomerInfo($orderData['customer'] ?? []);
 
            if (!$customer) {
                return response()->json(['error' => 'Customer update failed'], 400);
            }

            $this->handleOrderProducts($customer, $orderData['products'] ?? []);

          //  event(new WordpressOrderUpdated($req, $customer, $orderData));

            return $customer->load('address', 'contact');
        } catch (Throwable $ex) {

       
            Log::error($ex->getMessage());
            return $this->errorResponse($ex);
        }
    }


    /**
     * Slet en ordre
     */
    public function destroy(Request $req)
    {
        


        $orderData = $req->all();
        $orderId = $req->input('order_id');

        try {
            $customer = $this->getCustomerByEmail($orderData['customer']['billing']['email'] ?? null);

            if (!$customer) {
                Log::info("No customer found to delete order for");
                return response()->noContent();
            }

            $this->unsubscribeOrderProducts($customer, $orderData['products'] ?? []);

          //  event(new WordpressOrderDeleted($req, $customer, $orderData));

            return response()->noContent();
        } catch (Throwable $ex) {
            Log::error($ex->getMessage());
            return $this->errorResponse($ex);
        }
    }



    /***************************
     * PRIVATE HELPERS
     ***************************/


    private function createOrGetCustomer(array $data)
    {


        $billing = $data['billing'] ?? null;

        if (!$billing) return null;


        $customer = User::where('email', $billing['email'] ?? null)->first();


        if (!$customer) {
            
            $userData = array_merge($billing, ['send_activation' => true]);

            $customer = app(UserService::class)->create($userData);

        }


        $this->createOrUpdateAddress($customer, $billing);

        $this->createOrUpdateContact($customer, $billing);

        
        return $customer->refresh();

    }



    private function updateCustomerInfo(array $data)
    {

        $billing = $data['billing'] ?? null;

        if (!$billing) return null;

        $customer = User::where('email', $billing['email'] ?? null)->first();

        if (!$customer) return null;

        $customer->update([
            'first_name' => $billing['first_name'] ?? $customer->first_name,
            'last_name' => $billing['last_name'] ?? $customer->last_name,
        ]);

        $this->createOrUpdateAddress($customer, $billing);
        $this->createOrUpdateContact($customer, $billing);

        return $customer->refresh();


    }


    private function getCustomerByEmail(?string $email)
    {

        if (!$email) return null;

        return User::where('email', $email)->first();

    }


    private function handleOrderProducts(User $customer, array $products)
    {
        $orders = [];
        foreach ($products as $product) {
            if (!isset($product['slug'])) continue;

            $plan = Plan::findBySku($product['slug']);
            if ($plan) {
                $customer->subscriptions()->create([
                    'plan_id' => $plan->id,
                    'starts_at' => $product['starts_at'] ?? now(),
                    'ends_at' => $product['ends_at'] ?? null,
                ]);
            }

            $orders[] = $product;
        }
        return $orders;
        
    }



    private function unsubscribeOrderProducts(User $customer, array $products)
    {

        foreach ($products as $product) {


            if (!isset($product['slug'])) continue;

            $plan = Plan::findBySku($product['slug']);

            if (!$plan) continue;

            $subscription = $customer->subscriptions()->where('plan_id', $plan->id)->latest()->first();

            if ($subscription) {
                $subscription->delete();
            }

        }

    }


    private function createOrUpdateAddress(User $customer, array $billing)
    {

        if (empty($billing['address'])) return;


        $customer->addresses()->updateOrCreate(
            ['is_primary' => true],
            [
                'street' => $billing['address'] ?? null,
                'address_2' => $billing['address_2'] ?? null,
                'post_code' => $billing['postcode'] ?? null,
                'city' => $billing['city'] ?? null,
                'state' => $billing['state'] ?? null,
                'country_id' => $this->getCountryId($billing['country'] ?? null),
                'is_billing' => true,
                'is_shipping' => true,
            ]
        );

    }



    private function createOrUpdateContact(User $customer, array $billing)
    {

        $contact = $customer->contacts()->where('type', 'default')->first();

        $data = [
            'type' => 'default',
            'first_name' => $billing['first_name'] ?? null,
            'middle_name' => $billing['middle_name'] ?? null,
            'last_name' => $billing['last_name'] ?? null,
            'company' => $billing['company'] ?? null,
            'vat_id' => $billing['vat'] ?? null,
            'phone' => $billing['phone'] ?? null,
            'email' => $billing['email'] ?? null,
            'address_id' => $customer->addresses()->first()->id ?? null,
            'contactable_type' => 'User',
            'contactable_id' => $customer->id,
            'is_public' => 0,
            'is_primary' => 1,
            'notes' => $billing['note'] ?? null,
        ];


        if ($contact) {
            $contact->update($data);
        } else {
            $customer->contacts()->create($data);
        }

    }

    
    private function getCountryId(?string $countryCode)
    {
        if (!$countryCode) return null;

        return Countries::where('calling_code', strtoupper($countryCode))->first()->id ?? null;
    }

    private function errorResponse(Throwable $ex)
    {
        return response()->json([
            'error' => $ex->getMessage(),
            'file' => $ex->getFile(),
            'line' => $ex->getLine(),
        ], 500);
    }
}
