<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'product_id', 'purchase_price', 'quantity', 'unit',
        'subtotal_price', 'created_by', 'updated_by'
    ];


    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $updateData = request('components')[0]['snapshot'];

            $snapshotData = json_decode($updateData, true);



            if (!empty($snapshotData)){
                $status = $snapshotData['data']['data'][0]['status'];
            }else{
                $status = null;
            }

            if ($status == 'completed'){
                $batchNo = StockBalance::getBatchNo($model->purchase->id, $model->product_id);
                $exitTransaction = StockBalance::where( 'transaction_id', $model->purchase_id);

                $product = Product::where('id', $model->product_id)->first();

                if ($product) {
                    $product->increment('stock', $model->quantity);
                    $product->save();
                }

                if ($exitTransaction->exists()){
                    $update = $exitTransaction->first();
                    $update->purchase_quantity = $model->quantity;
                    $update->current_quantity = $model->quantity;
                    $update->save();
                }else{
                    StockBalance::create([
                        'transaction_id' => $model->purchase->id,
                        'product_id' => $model->product_id,
                        'batch_no' => $batchNo,
                        'purchase_quantity' =>  $model->quantity,
                        'unit' =>  'pieces',
                        'sale_price' =>  $model->purchase_price,
                        'current_quantity'  => $model->quantity,
                    ]);
                }
            }

            if ($status != null && $status  != 'completed'){
                StockBalance::where('transaction_id', $model->purchase->id)
                    ->whereColumn('purchase_quantity', 'current_quantity')
                    ->delete();

            }


        });

    }


    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
