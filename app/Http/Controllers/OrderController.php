<?php

namespace App\Http\Controllers;

use App\Interfaces\PaymentService;
use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{

    protected $paymentGateway ;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentGateway  = $paymentService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Retrieve all orders
        $orders = Order::when($request->filled('customer'), function (Builder $query) use ($request) {
            return $query->where('customer', $request->customer);
        })->get();

        return successResponse('success', $orders);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer' => 'required|integer|exists:customers,id',
            'products' => 'required|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return errorResponse('Validation Failed', $validator->errors(), 400);
        }

        // create an order and then attach the product sent with request
        $products = $request->products;
        $order = Order::create(['customer' => $request->customer]);
        $order->products()->attach($products);

        return successResponse('success', $order, 201);
    }

    /**
     * Display the specified order resource.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): \Illuminate\Http\JsonResponse
    {
        $order = Order::with('products')->find($id);
        return successResponse('success', $order);
    }

    /**
     * Update the specified order and it's product by detaching the old product.
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Order $order): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array',
            'products.*' => 'integer|exists:products,id',
        ]);

        if ($validator->fails()) {
            return errorResponse('Validation Failed', $validator->errors(), 400);
        }

        $order->products()->sync($request->products);
        return successResponse('success', $order);
    }

    /**
     * Remove the specified resource from storage.
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return errorResponse('Invalid order id is given');
        }
        $order->delete();
        return successResponse('success', [], 202);
    }


    /**
     * Attach Product to an existing Order
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function attachProduct(Request $request, Order $order)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id'
        ]);

        if ($validator->fails()) {
            return errorResponse('Validation Failed', $validator->errors(), 400);
        }

        if ($order->payed) {
            return errorResponse('order already placed, can\'t attach any more product');
        }

        $order->products()->attach($request->product_id);
        return successResponse('product attached successfully');
    }


    /**
     * Attach Product to an existing Order
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function CheckoutOrder($id): \Illuminate\Http\JsonResponse
    {

        $order = Order::query()->with(['customer_details', 'products'])->find($id);

        if (!$order) {
            return errorResponse('invalid Order Id' );
        }

        if ($order->payed) {
            return errorResponse('order already processed, can\'t pay again');
        }

        $requestData = [
            'order_id' => 23, // other order id is not supported so passing what is in documentation as statically
//            'order_id' => $id,
            'customer_email' => $order->customer_details->email,
            'value' => $order->products->sum('price')
        ];

        $payment = $this->paymentGateway->makePayment($order,$requestData);

        if ($payment['status']) {
            return successResponse($payment['message']);
        }
        return errorResponse($payment['message']);
    }


}
