<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Observers\OrderObserver;
use App\Observers\OrderItemObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register repository binding
        $this->app->bind(
            \App\Contracts\Repositories\OrderRepositoryInterface::class,
            \App\Repositories\OrderRepository::class
        );

        // Register service binding
        $this->app->bind(
            \App\Contracts\Services\OrderServiceInterface::class,
            \App\Services\OrderService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Order::observe(OrderObserver::class);
        OrderItem::observe(OrderItemObserver::class);
    }
}
