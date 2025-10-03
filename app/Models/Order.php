<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'guest_name',
        'table_number',
        'status',
        'total_price',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Define status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_COMPLETED = 'completed';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class)->with('menu');
    }

    public function menuItems()
    {
        return $this->belongsToMany(Menu::class, 'order_items')
                    ->withPivot('quantity', 'price')
                    ->withTimestamps();
    }
    
    /**
     * Check if order is placed by a guest (without user account)
     */
    public function isGuestOrder(): bool
    {
        return $this->user_id === null && !empty($this->guest_name);
    }
}
