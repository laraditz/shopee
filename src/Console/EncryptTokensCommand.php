<?php

namespace Laraditz\Shopee\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Laraditz\Shopee\Models\ShopeeAccessToken;

class EncryptTokensCommand extends Command
{
    protected $signature = 'shopee:encrypt-tokens';

    protected $description = 'Encrypt existing plaintext Shopee access and refresh tokens.';

    public function handle()
    {
        if (!config('shopee.encrypt_tokens', false)) {
            $this->error('Enable shopee.encrypt_tokens first.');

            return 1;
        }

        $count = 0;

        ShopeeAccessToken::query()->chunkById(100, function ($rows) use (&$count) {
            foreach ($rows as $row) {
                $updates = [];

                foreach (['access_token', 'refresh_token'] as $column) {
                    $raw = $row->getRawOriginal($column);

                    if ($raw === null || $this->isEncrypted($raw)) {
                        continue;
                    }

                    $updates[$column] = Crypt::encryptString($raw);
                    $count++;
                }

                if ($updates) {
                    DB::table($row->getTable())
                        ->where($row->getKeyName(), $row->getKey())
                        ->update($updates);
                }
            }
        });

        $this->info("Encrypted {$count} value(s).");

        return 0;
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (DecryptException $e) {
            return false;
        }
    }
}
