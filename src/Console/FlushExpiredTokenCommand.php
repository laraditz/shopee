<?php

namespace Laraditz\Shopee\Console;

use Illuminate\Console\Command;
use Laraditz\Shopee\Models\ShopeeAccessToken;

class FlushExpiredTokenCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopee:flush-expired-token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush expired access token.';

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
        if (!$this->shouldDelete()) {
            $this->info('You have cancelled the command.');
            return;
        }

        $query = $this->getQuery();

        $query->lazy()->each(function ($item) {
            $this->info(__('<fg=yellow>Deleting :entity access token.</>', ['entity' => optional($item->entity)->name ?? '']));
            if ($item->delete()) {
                $this->info(__(':entity access token was deleted.', ['entity' => optional($item->entity)->name ?? 'The']));
            }
        });

        $this->newLine();
        $this->info('Expired access tokens were deleted.');
    }

    private function getQuery()
    {
        $query = ShopeeAccessToken::query();

        $query->where('expires_at', '<=', now());

        return $query;
    }

    private function shouldDelete()
    {
        return $this->confirm(
            'You are about to remove expired Shopee access tokens. Continue?',
            false
        );
    }
}
