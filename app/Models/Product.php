<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;


    protected $fillable = [
        'sku',
        'name',
        'description',
        'purchase_price',
        'sale_price',
        'stock',
        'unit',
        'created_by',
        'updated_by',
    ];

    public static function generateSku()
    {
        $randomNumber = random_int(10000000, 99999999);
        return 'SKU-' . $randomNumber;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_by = Auth::id();
        });

        static::updating(function ($model) {
            $model->updated_by = Auth::id();
        });

    }


    public function purchase_details()
    {
        return $this->hasMany(PurchaseDetail::class, 'product_id', 'id');
    }

    public function sale_details()
    {
        return $this->hasMany(SaleDetail::class, 'product_id', 'id');
    }


    // Define the relationship for created_by
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Define the relationship for updated_by
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
