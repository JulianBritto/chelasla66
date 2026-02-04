<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Invoice;

class SoldProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'invoice_number',
        'product_id',
        'quantity',
        'price_total',
        'invoice_date'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
