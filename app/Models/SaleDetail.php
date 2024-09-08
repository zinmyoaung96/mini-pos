<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id', 'product_id', 'sale_price', 'quantity', 'unit',
        'subtotal_price', 'created_by', 'updated_by'
    ];

    protected function mutateFormDataBeforeFill(array $data): array
    {

        $data['current_qty'] =11;
        return $data;
    }

    protected static function boot()
    {
        parent::boot();




        static::created(function ($model) {


            $sale = Sale::where('id', $model->sale_id)->first();


            if ($sale->status == 'served' || $sale->status == 'completed'){
                $currentStocks = StockBalance::where('product_id', $model->product_id)
                    ->where('current_quantity', '>', 0)
                    ->get();

                $quantity = $model->quantity;

                foreach ($currentStocks as $stock) {
                    if ($quantity >= $stock->current_quantity) {
                        $quantity -= $stock->current_quantity;
                        $stock->current_quantity = 0;
                    } else {
                        $stock->current_quantity = $stock->current_quantity - $quantity;
                        $quantity = 0;
                    }

                    $stock->save();

                    if ($quantity == 0) {
                        break;
                    }
                }
            }




            $model->created_by = Auth::id();
            $model->updated_by = Auth::id();
        });

//        static::updated(function ($model) {
//
//
//            $sale = Sale::where('id', $model->sale_id)->first();
//
//            $updateData = request('components')[0]['snapshot'];
//
//            $snapshotData = json_decode($updateData, true);
//
//            if (!empty($snapshotData)){
//                $status = $snapshotData['data']['data'][0]['status'];
//            }else{
//                $status = null;
//            }
//
//
//        });

    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(){
        return $this->belongsTo(Product::class);
    }
}
