<?php
// app/Exports/WarehouseProductsExport.php

namespace App\Exports;

use App\Models\Price;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithTitle;

class WarehouseProductsExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle
{
    protected $warehouseId;
    protected $stockStatus;
    protected $categoryId;
    protected $brandId;

    public function __construct($warehouseId, $stockStatus = 'all', $categoryId = null, $brandId = null)
    {
        $this->warehouseId = $warehouseId;
        $this->stockStatus = $stockStatus;
        $this->categoryId = $categoryId;
        $this->brandId = $brandId;
    }

    public function query()
    {
        $query = Price::with(['product', 'product.category', 'product.brand'])
            ->where('warehouse_id', $this->warehouseId);

        // فیلتر بر اساس موجودی
        if ($this->stockStatus == 'in_stock') {
            $query->where('stock', '>', 0);
        } elseif ($this->stockStatus == 'out_of_stock') {
            $query->where('stock', 0);
        }

        // فیلتر بر اساس دسته‌بندی
        if ($this->categoryId) {
            $query->whereHas('product', function($q) {
                $q->where('category_id', $this->categoryId);
            });
        }

        // فیلتر بر اساس برند
        if ($this->brandId) {
            $query->whereHas('product', function($q) {
                $q->where('brand_id', $this->brandId);
            });
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function headings(): array
    {
        return [
            'ردیف',
            'کد محصول',
            'عنوان محصول',
            'دسته‌بندی',
            'برند',
            'قیمت (تومان)',
            'موجودی',
            'رزرو شده',
            'تعداد فروش',
            'کد انبار',
            'نام انبار',
            'موقعیت در انبار',
            'تاریخ آخرین بروزرسانی',
        ];
    }

    public function map($price): array
    {
        static $row = 0;
        $row++;

        return [
            $row,
            $price->product->id ?? '-',
            $price->product->title ?? '-',
            $price->product->category->title ?? '-',
            $price->product->brand->name ?? '-',
            number_format($price->price),
            $price->stock,
            $price->reserved_stock,
            $price->sold_count,
            $price->warehouse->code ?? '-',
            $price->warehouse->name ?? '-',
            $price->location_code ?? '-',
            $price->last_stock_update ? jdate($price->last_stock_update)->format('Y/m/d H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            'A' => ['width' => 10],
            'B' => ['width' => 15],
            'C' => ['width' => 40],
            'D' => ['width' => 20],
            'E' => ['width' => 20],
            'F' => ['width' => 20],
            'G' => ['width' => 15],
            'H' => ['width' => 15],
            'I' => ['width' => 15],
            'J' => ['width' => 15],
            'K' => ['width' => 25],
            'L' => ['width' => 20],
            'M' => ['width' => 20],
        ];
    }

    public function title(): string
    {
        return 'لیست محصولات انبار';
    }
}
