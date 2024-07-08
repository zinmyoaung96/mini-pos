<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StockBalance extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id','product_id', 'batch_no', 'purchase_quantity', 'unit',
        'sale_price', 'current_quantity', 'created_by', 'updated_by'
    ];

    public function scopeMergedStocks(Builder $query): array
    {

         return  $query->select('query_id', 'unit')
            ->selectRaw('SUM(current_quantity) as total_current_quantity')
            ->selectRaw('SUM(purchase_quantity) as total_purchase_quantity')
            ->groupBy('query_id', 'unit')
            ->get();
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

    public static function getBatchNo($transactionId, $productId)
    {
        $prefix = 'Batch';
        $count = 4;

        $lastCurrentStockCount = StockBalance::select('id','batch_no','product_id')->where('product_id', $productId)->OrderBy('id', 'DESC')->first()->batch_no ?? 0;

        $numberCount = "%0" . $count . "d";
        $seperator=$prefix ? '-' :'';
        $exploded=explode('-',$lastCurrentStockCount);
        $lastNo=intval(end($exploded));
        $batchNo = sprintf($prefix.$seperator. $numberCount, ($lastNo + 1));
        return $batchNo;

    }

    public static function generateBatchNo($variation_id, $prefix = "", $count = 6)
    {
        $lastCurrentStockCount = CurrentStockBalance::select('id','batch_no','variation_id')->where('variation_id',$variation_id)->OrderBy('id', 'DESC')->first()->batch_no ?? 0;

        $numberCount = "%0" . $count . "d";
        $seperator=$prefix ? '-' :'';
        $exploded=explode('-',$lastCurrentStockCount);
        $lastNo=intval(end($exploded));
        $batchNo = sprintf($prefix.$seperator. $numberCount, ($lastNo + 1));
        return $batchNo;
    }

    public function transaction()
    {
        return $this->belongsTo(Purchase::class, 'transaction_id', 'id');
    }


    public function product()
    {
        return $this->belongsTo(Product::class);
    }

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
