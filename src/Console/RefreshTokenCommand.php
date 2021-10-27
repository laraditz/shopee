<?php

namespace Laraditz\Shopee\Console;

use Illuminate\Console\Command;
use Laraditz\Shopee\Models\ShopeeAccessToken;

class RefreshTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopee:refresh-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh existing access token before it expired.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $query = $this->getQuery();

        $query->lazy()->each(function ($item) {
            $this->info(__('<fg=yellow>Refreshing :entity access token.</>', ['entity' => optional($item->entity)->name ?? '']));
            app('shopee')->auth()->refreshToken($item);
            $this->info(__(':entity access token was refresh.', ['entity' => optional($item->entity)->name ?? 'The']));
        });
    }

    private function getQuery()
    {
        $query = ShopeeAccessToken::query();

        $query->where('expires_at', '>', now());

        return $query;
    }
}
