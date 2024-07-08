<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'supplier_id', 'purchase_date', 'status', 'received_date',
        'remark', 'total_price', 'payment_type', 'items', 'created_by', 'updated_by'
    ];

    public static function generateVoucherNo()
    {
        $purchaseCount = Purchase::count();
        return 'P-' . str_pad($purchaseCount, 6, '0', STR_PAD_LEFT);
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

    public function details()
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function supplier(){
        return $this->belongsTo(Contact::class)->where('type','supplier');
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }


    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
