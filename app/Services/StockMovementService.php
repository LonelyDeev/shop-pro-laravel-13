<?php

namespace App\Services;

use App\Models\Price;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementService
{
    /**
     * ثبت ورود کالا به انبار
     *
     * @param Price $price
     * @param int $quantity
     * @param string|null $description
     * @param string|null $reference
     * @param int|null $orderId
     * @return StockMovement
     * @throws \Exception
     */
    public function inbound(Price $price, int $quantity, ?string $description = null, ?string $reference = null, ?int $orderId = null): StockMovement
    {
        return DB::transaction(function () use ($price, $quantity, $description, $reference, $orderId) {
            $beforeStock = $price->stock;
            $afterStock = $beforeStock + $quantity;

            // بروزرسانی موجودی
            $price->increment('stock', $quantity);

            // ثبت حرکت
            $movement = StockMovement::create([
                'product_id' => $price->product->id,
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'order_id' => $orderId,
                'type' => 'in',
                'quantity' => $quantity,
                'before_stock' => $beforeStock,
                'after_stock' => $afterStock,
                'reference' => $reference,
                'description' => $description ?? "ورود {$quantity} عدد کالا به انبار",
                'operator_type' => $this->getOperatorType(),
                'operator_id' => $this->getOperatorId(),
                'attributes'=>$this->getAttributes($price),
            ]);

            Log::info("ورود کالا به انبار", [
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'quantity' => $quantity,
                'operator' => auth()->id()
            ]);

            return $movement;
        });
    }

    /**
     * ثبت خروج کالا از انبار (فروش)
     *
     * @param Price $price
     * @param int $quantity
     * @param int|null $orderId
     * @param int|null $orderItemId
     * @param string|null $description
     * @return StockMovement
     * @throws \Exception
     */
    public function outbound(Price $price, int $quantity, ?int $orderId = null, ?int $orderItemId = null, ?string $description = null): StockMovement
    {
        return DB::transaction(function () use ($price, $quantity, $orderId, $orderItemId, $description) {
            // بررسی موجودی کافی
            if ($price->stock < $quantity) {
                throw new \Exception("موجودی کافی نیست. موجودی فعلی: {$price->stock} - درخواست: {$quantity}");
            }

            $beforeStock = $price->stock;
            $afterStock = $beforeStock - $quantity;

            // بروزرسانی موجودی و فروش
            $price->decrement('stock', $quantity);
            $price->increment('sold_count', $quantity);

            // ثبت حرکت
            $movement = StockMovement::create([
                'product_id' => $price->product->id,
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'type' => 'out',
                'quantity' => $quantity,
                'before_stock' => $beforeStock,
                'after_stock' => $afterStock,
                'description' => $description ?? "خروج {$quantity} عدد کالا از انبار",
                'operator_type' => $this->getOperatorType(),
                'operator_id' => $this->getOperatorId(),
                'attributes'=>$this->getAttributes($price),
            ]);

            Log::info("خروج کالا از انبار", [
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'quantity' => $quantity,
                'order_id' => $orderId,
                'operator' => auth()->id()
            ]);

            return $movement;
        });
    }

    /**
     * رزرو موجودی (برای ثبت سفارش)
     *
     * @param Price $price
     * @param int $quantity
     * @param int|null $orderId
     * @param int|null $orderItemId
     * @return StockMovement
     * @throws \Exception
     */
    public function reserve(Price $price, int $quantity, ?int $orderId = null, ?int $orderItemId = null): StockMovement
    {
        return DB::transaction(function () use ($price, $quantity, $orderId, $orderItemId) {

            // ========== بررسی رزرو قبلی برای همین سفارش و آیتم ==========
            if ($orderId && $orderItemId) {
                $existingReservation = StockMovement::where('order_id', $orderId)
                    ->where('order_item_id', $orderItemId)
                    ->where('type', 'reserve')
                    ->first();

                if ($existingReservation) {
                    Log::warning("رزرو قبلی وجود دارد", [
                        'order_id' => $orderId,
                        'order_item_id' => $orderItemId,
                        'existing_quantity' => $existingReservation->quantity
                    ]);

                    // برگرداندن رزرو قبلی به جای ایجاد رزرو جدید
                    return $existingReservation;
                }
            }

            // بررسی رزرو برای کل سفارش (بدون آیتم)
            if ($orderId && !$orderItemId) {
                $existingReservation = StockMovement::where('order_id', $orderId)
                    ->where('type', 'reserve')
                    ->whereNull('order_item_id')
                    ->first();

                if ($existingReservation) {
                    Log::warning("رزرو قبلی برای سفارش وجود دارد", [
                        'order_id' => $orderId
                    ]);
                    return $existingReservation;
                }
            }

            $availableStock = $price->stock - $price->reserved_stock;

            if ($availableStock < $quantity) {
                throw new \Exception("موجودی قابل رزرو کافی نیست. موجودی قابل رزرو: {$availableStock} - درخواست: {$quantity}");
            }

            $beforeReserved = $price->reserved_stock;
            $afterReserved = $beforeReserved + $quantity;

            $price->increment('reserved_stock', $quantity);

            $movement = StockMovement::create([
                'product_id' => $price->product->id,
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'type' => 'reserve',
                'quantity' => $quantity,
                'before_stock' => $price->stock,
                'after_stock' => $price->stock,
                'description' => "رزرو {$quantity} عدد کالا برای سفارش شماره {$orderId}",
                'operator_type' => $this->getOperatorType(),
                'operator_id' => $this->getOperatorId(),
                'attributes' => $this->getAttributes($price),
            ]);

            Log::info("رزرو کالا", [
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'quantity' => $quantity,
                'order_id' => $orderId,
                'movement_id' => $movement->id
            ]);

            return $movement;
        });
    }

    /**
     * لغو رزرو
     *
     * @param Price $price
     * @param int $quantity
     * @param int|null $orderId
     * @param int|null $orderItemId
     * @return StockMovement
     */
    public function unreserve(Price $price, int $quantity, ?int $orderId = null, ?int $orderItemId = null): StockMovement
    {
        return DB::transaction(function () use ($price, $quantity, $orderId, $orderItemId) {
            $beforeReserved = $price->reserved_stock;
            $afterReserved = max(0, $beforeReserved - $quantity);

            // بروزرسانی موجودی رزرو شده
            $price->decrement('reserved_stock', $quantity);

            // ثبت حرکت
            $movement = StockMovement::create([
                'product_id' => $price->product->id,
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'order_id' => $orderId,
                'order_item_id' => $orderItemId,
                'type' => 'unreserve',
                'quantity' => $quantity,
                'before_stock' => $price->stock,
                'after_stock' => $price->stock,
                'description' => "لغو رزرو {$quantity} عدد کالا از سفارش شماره {$orderId}",
                'operator_type' => $this->getOperatorType(),
                'operator_id' => $this->getOperatorId(),
                'attributes'=>$this->getAttributes($price),
            ]);

            Log::info("لغو رزرو کالا", [
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'quantity' => $quantity,
                'order_id' => $orderId
            ]);

            return $movement;
        });
    }

    /**
     * تایید رزرو و تبدیل به فروش واقعی
     *
     * @param Price $price
     * @param int $quantity
     * @param int|null $orderId
     * @param int|null $orderItemId
     * @return StockMovement
     */
    public function confirmReservation(Price $price, int $quantity, ?int $orderId = null, ?int $orderItemId = null): StockMovement
    {
        return DB::transaction(function () use ($price, $quantity, $orderId, $orderItemId) {
            // ابتدا رزرو را لغو می‌کنیم
            $this->unreserve($price, $quantity, $orderId, $orderItemId);

            // سپس خروج واقعی را ثبت می‌کنیم
            return $this->outbound($price, $quantity, $orderId, $orderItemId, "تایید سفارش و خروج کالا از انبار");
        });
    }

    /**
     * تنظیم دستی موجودی
     *
     * @param Price $price
     * @param int $newStock
     * @param string|null $description
     * @return StockMovement
     */
    public function adjustment(Price $price, int $newStock, ?string $description = null): StockMovement
    {

        return DB::transaction(function () use ($price, $newStock, $description) {
            $beforeStock = $price->stock;
            $quantity = abs($newStock - $beforeStock);
            $type = $newStock > $beforeStock ? 'in' : 'out';

            // بروزرسانی موجودی
            $price->update(['stock' => $newStock]);

            // ثبت حرکت
            $movement = StockMovement::create([
                'product_id' => $price->product->id,
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'type' => 'adjustment',
                'quantity' => $quantity,
                'before_stock' => $beforeStock,
                'after_stock' => $newStock,
                'description' => $description ?? "تنظیم دستی موجودی از {$beforeStock} به {$newStock}",
                'operator_type' => $this->getOperatorType(),
                'operator_id' => $this->getOperatorId(),
                'attributes'=>$this->getAttributes($price),
            ]);

            Log::warning("تنظیم دستی موجودی", [
                'price_id' => $price->id,
                'warehouse_id' => $price->warehouse_id,
                'old_stock' => $beforeStock,
                'new_stock' => $newStock,
                'operator' => auth()->id()
            ]);

            return $movement;
        });
    }

    /**
     * آزادسازی رزروهای قبلی یک سفارش قبل از رزرو مجدد
     */
    public function releasePreviousReservations(int $orderId, ?int $orderItemId = null): void
    {
        DB::transaction(function () use ($orderId, $orderItemId) {
            $query = StockMovement::where('order_id', $orderId)
                ->where('type', 'reserve')
                ->whereIn('status', ['pending', 'reserved']);

            if ($orderItemId) {
                $query->where('order_item_id', $orderItemId);
            }

            $reservations = $query->get();

            foreach ($reservations as $reservation) {
                $price = Price::find($reservation->price_id);
                if ($price) {
                    // کاهش موجودی رزرو شده
                    $price->decrement('reserved_stock', $reservation->quantity);

                    // بروزرسانی وضعیت رزرو
                    $reservation->update([
                        'status' => 'released',
                        'description' => $reservation->description . " - آزادسازی شده به دلیل درخواست مجدد"
                    ]);

                    Log::info("آزادسازی رزرو قبلی", [
                        'order_id' => $orderId,
                        'price_id' => $price->id,
                        'quantity' => $reservation->quantity,
                        'movement_id' => $reservation->id
                    ]);
                }
            }
        });
    }

    /**
     * دریافت موجودی قابل فروش
     *
     * @param Price $price
     * @return int
     */
    public function getAvailableStock(Price $price): int
    {
        return $price->stock - $price->reserved_stock;
    }

    /**
     * دریافت نوع اپراتور
     *
     * @return string|null
     */
    protected function getOperatorType(): ?string
    {
        if (!auth()->check()) {
            return 'system';
        }

        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return 'admin';
        }

        if ($user->hasRole('seller')) {
            return 'seller';
        }

        return 'system';
    }

    /**
     * دریافت آیدی اپراتور
     *
     * @return int|null
     */
    protected function getOperatorId(): ?int
    {
        return auth()->id();
    }

    private function getAttributes($price)
    {

        $get_attributes = $price->get_attributes()->with('group')->get();

        $allAttributes=[];
        foreach ($get_attributes as $attribute) {
            $attribute_groups_name = $attribute->group->name;

            // اگر گروه قبلاً وجود دارد، به آرایه اضافه کن
            if (!isset($allAttributes[$attribute_groups_name])) {
                $allAttributes[$attribute_groups_name] = [];
            }

            $allAttributes[$attribute_groups_name][] = [
                'name' => $attribute->name,
                'value' => $attribute->value,
            ];
        }

        $allAttributes = json_encode($allAttributes);
        return $allAttributes;
    }
}
