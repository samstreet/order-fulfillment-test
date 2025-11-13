<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\Services\OrderServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
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
    public function show(int $id): JsonResponse
    {
        $order = $this->orderService->getOrderById($id);

        return (new OrderResource($order))->response();
    }

    /**
     * Update the status of the specified order.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus($id, $request->getStatus());

        return (new OrderResource($order))->response();
    }

    /**
     * Remove the specified order.
     */
    public function destroy(int $id): JsonResponse
    {
        $this->orderService->deleteOrder($id);

        return response()->json([
            'message' => 'Order deleted successfully',
        ]);
    }
}
