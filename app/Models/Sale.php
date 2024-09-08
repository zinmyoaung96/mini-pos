<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_no', 'customer_id', 'floor_id', 'table_id', 'sale_date', 'payment_status', 'remark',
        'total_price', 'payment_type','status', 'total_price', 'payment_type',
        'paid_amount', 'balance_amount', 'refund_amount', 'created_by', 'updated_by'
    ];

    protected static function boot()
    {
        parent::boot();
//        Log::debug('Sale Model Begin');
        static::created(function ($model) {


            if ($model->status == 'completed' || $model->status == 'cancelled') {
                Table::where('id', $model->table_id)->update([
                    'is_use' => false
                ]);
            }else{
                Table::where('id', $model->table_id)->update([
                    'is_use' => true
                ]);
            }

            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

        static::updating(function ($model) {

            $oldStatus = $model->getOriginal('status');
            $newStatus = $model->status;


            Log::debug($oldStatus);
            Log::debug($newStatus);

            if ($model->status == 'completed' || $model->status == 'cancelled') {
                Table::where('id', $model->table_id)->update([
                    'is_use' => false
                ]);
            }else{
                Table::where('id', $model->table_id)->update([
                    'is_use' => true
                ]);
            }

            $details = $model->details;

            if ($newStatus != $oldStatus) {
                if ($newStatus == 'served' || $newStatus == 'completed') {
                    DB::transaction(function () use ($model, $details) {

                        foreach ($details as $detail) {
                            // Set the flag to prevent recursion

                            // Get the current stock balances for the product
                            $currentStocks = StockBalance::where('product_id', $detail->product_id)
                                ->where('current_quantity', '>', 0)
                                ->orderBy('created_at')
                                ->get();

                            $quantity = $detail->quantity;

                            foreach ($currentStocks as $stock) {
                                if ($quantity <= 0) {
                                    break;
                                }

                                if ($quantity >= $stock->current_quantity) {
                                    $quantity -= $stock->current_quantity;
                                    $stock->current_quantity = 0;
                                } else {
                                    $stock->current_quantity -= $quantity;
                                    $quantity = 0;
                                }

                                $stock->save();
                            }

                            $currentStocks = StockBalance::where('product_id', $detail->product_id)
                                ->where('current_quantity', '>', 0)
                                ->sum('current_quantity');

                            Product::where('id', $detail->product_id)->update([
                                'stock' => $currentStocks
                            ]);

                        }
                    });
                }
            }

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
