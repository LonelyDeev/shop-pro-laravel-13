<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarehouseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $warehouse;
    protected $products;

    public function __construct($warehouse, $products)
    {
        $this->warehouse = $warehouse;
        $this->products = $products;
    }

    public function collection()
    {
        return $this->products;
    }

    public function headings(): array
    {
        return [
            'شناسه محصول',
            'عنوان محصول',
            'دسته‌بندی',
            'برند',
            'قیمت',
            'تخفیف',
            'قیمت نهایی',
            'موجودی',
            'تعداد فروش',
        ];
    }

    public function map($product): array
    {
        $price = $product->prices->first();

        return [
            $product->id,
            $product->title,
            $product->category->title ?? '-',
            $product->brand->name ?? '-',
            number_format($price->price ?? 0),
            ($price->discount ?? 0) . '%',
            number_format($price->discount_price ?? $price->price ?? 0),
            $price->stock ?? 0,
            $price->sold_count ?? 0,
        ];
    }
}
