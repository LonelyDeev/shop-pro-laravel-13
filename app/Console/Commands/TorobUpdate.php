<?php

namespace App\Console\Commands;

use App\Models\Torob;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TorobUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'torob:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Torob Prices';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $torobs = Torob::whereHas('product', function ($query) {
                $query->published()->available();
            })
                ->where(function ($query) {
                    $query->where(
                        'last_update',
                        '<',
                        now()->subHours(24 * 4)
                    )->orWhereNull('last_update');
                })
                ->where('check_torob', true)
                ->oldest('last_update')
                ->take(10)
                ->get();

            foreach ($torobs as $torob) {
                $product = $torob->product;

                if (!$product) {
                    Log::warning('Torob product not found.', [
                        'torob_id' => $torob->id,
                    ]);

                    continue;
                }

                // Clear previous Torob data before checking new prices.
                $torob->update([
                    'price'             => null,
                    'last_price_change' => null,
                    'price_link'        => null,
                    'shop_name'         => null,
                    'title'             => null,
                    'torob_link_id'     => null,
                    'review_need'       => false,
                ]);

                foreach ($torob->links as $link) {
                    $pattern = '/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i';

                    // Extract the UUID from the Torob URL.
                    if (!preg_match($pattern, $link->link, $matches)) {
                        Log::warning('Torob UUID not found in link.', [
                            'torob_id' => $torob->id,
                            'link_id'  => $link->id,
                            'link'     => $link->link,
                        ]);

                        continue;
                    }

                    $uuid = $matches[0];

                    $url = 'https://api.torob.com/v4/base-product/sellers/'
                        . '?source=next_desktop'
                        . '&discover_method=direct'
                        . '&_bt__experiment='
                        . '&search_id='
                        . '&cities='
                        . '&province='
                        . '&prk=' . urlencode($uuid)
                        . '&list_type=products_info';

                    try {
                        /*
                         * Use browser-like headers for the Torob request.
                         */
                        $response = Http::withHeaders([
                            // Standard request identification header
                            'User-Agent' => config('app.name', 'Laravel') . ' Price Monitor/1.0',
                            'Accept' => 'application/json',
                            'Accept-Language' => 'fa-IR,fa;q=0.9,en;q=0.8',
                            'Referer' => 'https://torob.com/',
                        ])
                            ->connectTimeout(15)
                            ->timeout(30)
                            ->get($url);

                        if ($response->failed()) {
                            Log::warning('Torob API request failed.', [
                                'torob_id' => $torob->id,
                                'link_id'  => $link->id,
                                'uuid'     => $uuid,
                                'status'   => $response->status(),
                                'body'     => mb_substr($response->body(), 0, 2000),
                                'url'      => $url,
                            ]);

                            continue;
                        }

                        $data = collect(
                            $response->json('results', [])
                        );

                        $torob->update([
                            'is_merged' => $data->count() > 1,
                        ]);

                        $lowestPrice = $data
                            ->filter(function ($item) {
                                return ($item['price_text_mode'] ?? null) === 'active'
                                    && isset($item['price'])
                                    && is_numeric($item['price']);
                            })
                            ->sortBy('price')
                            ->first();

                        $websiteLowestPrice = $product->getLowestPrice(true);

                        if (
                            $lowestPrice &&
                            $lowestPrice['price'] < $websiteLowestPrice &&
                            (
                                !$torob->price ||
                                $lowestPrice['price'] < $torob->price
                            )
                        ) {
                            $torob->update([
                                'price'             => $lowestPrice['price'],
                                'last_price_change' => $lowestPrice['last_price_change_date'] ?? null,
                                'price_link'        => $lowestPrice['page_url'] ?? null,
                                'shop_name'         => $lowestPrice['shop_name'] ?? null,
                                'title'             => $lowestPrice['name1'] ?? null,
                                'review_need'       => true,
                                'torob_link_id'     => $link->id,
                            ]);
                        }

                        // Avoid sending requests too quickly.
                        sleep(rand(10, 30));
                    } catch (Exception $exception) {
                        Log::error('Torob link update failed.', [
                            'torob_id' => $torob->id,
                            'link_id'  => $link->id,
                            'uuid'     => $uuid,
                            'message'  => $exception->getMessage(),
                            'url'      => $url,
                        ]);
                    }
                }

                /*
                 * Update the timestamp after all links are checked.
                 */
                $torob->update([
                    'last_update' => now(),
                ]);
            }

            Log::info('TorobUpdate completed successfully.', [
                'processed_count' => $torobs->count(),
            ]);

            return self::SUCCESS;
        } catch (Exception $exception) {
            Log::error('TorobUpdate failed.', [
                'message' => $exception->getMessage(),
            ]);

            return self::FAILURE;
        }
    }
}
