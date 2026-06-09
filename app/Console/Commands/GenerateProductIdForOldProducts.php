<?php
// app/Console/Commands/GenerateProductIdForOldProducts.php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class GenerateProductIdForOldProducts extends Command
{
    protected $signature = 'product:generate-old-ids';
    protected $description = 'Generate product_id for old products that don\'t have one';

    public function handle()
    {
        $products = Product::whereNull('product_id')->get();

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        foreach ($products as $product) {
            $product->product_id = $product->generateProductId();
            $product->saveQuietly(); // بدون trigger events
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Generated product_id for {$products->count()} products");

        return Command::SUCCESS;
    }
}
