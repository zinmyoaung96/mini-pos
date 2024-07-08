<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'customer_id', 'floor_id', 'table_id', 'sale_date', 'payment_status', 'remark',
        'total_price', 'payment_type','status', 'total_price', 'payment_type',
        'paid_amount', 'balance_amount', 'created_by', 'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            $model->total_price = 100;
            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function ($model) {
//            $model->updated_by = Auth::id();
        });

    }

    public static function generateVoucherNo()
    {
        $saleCount = Sale::count();
        return 'S-' . str_pad($saleCount == 0 ? 1 : $saleCount, 6, '0', STR_PAD_LEFT);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }

    public function details()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function customer(){
        return $this->belongsTo(Contact::class)->where('type','customer');
    }

    public function floor()
    {
        return $this->belongsTo(Floor::class);
    }

    public function table()
    {
        return $this->belongsTo(Table::class);
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
