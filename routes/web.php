<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return view('welcome');
//});


Route::get('/abc', function (){
// Build your query
    $query = \App\Models\StockBalance::query();

return $query->select(['transaction_id','product_id', 'batch_no', 'unit',
    'sale_price', 'created_by', 'updated_by'])
    ->selectRaw('SUM(current_quantity) as total_current_quantity', )
    ->selectRaw('SUM(purchase_quantity) as total_purchase_quantity')
//    ->groupBy('product_id', 'unit')
    ->get();
});
