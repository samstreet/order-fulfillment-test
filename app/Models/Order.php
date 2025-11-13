<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OrderStatus;
use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method static OrderFactory factory($count = null, $state = [])
 * @method static Builder<Order> byStatus(OrderStatus $status)
 * @method static Builder<Order> recent(int $days = 30)
 */
class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'order_number',
        'customer_name',
        'customer_email',
        'status',
        'notes',
        'ordered_at',
        'fulfilled_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => OrderStatus::class,
        'total_amount' => 'decimal:2',
        'items_count' => 'integer',
        'ordered_at' => 'datetime',
        'fulfilled_at' => 'datetime',
    ];

    /**
     * Get the order items for this order.
     *
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Scope a query to only include orders with a specific status.
     *
     * @param Builder<Order> $query
     * @return Builder<Order>
     */
    public function scopeByStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include recent orders.
     *
     * @param Builder<Order> $query
     * @return Builder<Order>
     */
    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('ordered_at', '>=', now()->subDays($days));
    }

    /**
     * Get the formatted total amount with currency symbol.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return '$' . number_format((float) $this->total_amount, 2);
    }

    /**
     * Get the formatted items count.
     */
    public function getFormattedItemsCountAttribute(): string
    {
        return $this->items_count . ' ' . str('item')->plural($this->items_count);
    }
}
