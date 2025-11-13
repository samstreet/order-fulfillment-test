<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    /**
     * @param OrderServiceInterface $orderService Service for order business logic
     */
    public function __construct(
        private readonly OrderServiceInterface $orderService
    ) {
    }

    /**
     * Display a listing of orders with optional filtering and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'search', 'page', 'per_page']);
        $orders = $this->orderService->getAllOrders($filters);

        if ($orders instanceof LengthAwarePaginator) {
            return OrderResource::collection($orders)->response();
        }

        return OrderResource::collection($orders)->response();
    }

    /**
     * Store a newly created order.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->createOrder($request->validated());

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): JsonResponse
    {
        return (new OrderResource($order->load('items')))->response();
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $updatedOrder = $this->orderService->updateOrderStatus($order->id, $request->getStatus());

        return (new OrderResource($updatedOrder))->response();
    }

    /**
     * Remove the specified order.
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->orderService->deleteOrder($order->id);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
