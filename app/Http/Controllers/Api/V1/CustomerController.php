<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckCustomerPhoneRequest;
use App\Http\Requests\Api\V1\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Check if a customer exists by phone number.
     */
    public function checkPhone(CheckCustomerPhoneRequest $request): JsonResponse
    {
        $customer = Customer::where('phone', $request->validated('phone'))->first();

        return response()->json([
            'data' => [
                'exists' => $customer !== null,
                'customer' => $customer ? new CustomerResource($customer) : null,
            ],
        ]);
    }

    /**
     * Get a customer by ID.
     */
    public function show(Customer $customer): CustomerResource
    {
        return new CustomerResource($customer);
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): CustomerResource
    {
        $customer = Customer::create($request->validated());

        return new CustomerResource($customer);
    }
}
