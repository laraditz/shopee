<?php

namespace Laraditz\Shopee\Tests\Unit;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Laraditz\Shopee\Models\ShopeeAccessToken;
use Laraditz\Shopee\Models\ShopeeShop;
use Laraditz\Shopee\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class TokenEncryptionTest extends TestCase
{
    private function make(string $access = 'AT', string $refresh = 'RT'): ShopeeAccessToken
    {
        $shop = ShopeeShop::create(['id' => random_int(1000, 999999)]);

        return $shop->accessToken()->create([
            'access_token' => $access,
            'refresh_token' => $refresh,
            'expires_at' => now()->addHour(),
        ]);
    }

    private function raw(ShopeeAccessToken $t): object
    {
        return DB::table('shopee_access_tokens')->where('id', $t->id)->first();
    }

    #[Test]
    public function test_default_stores_plaintext()
    {
        $t = $this->make();
        $this->assertSame('AT', $this->raw($t)->access_token);
        $this->assertSame('RT', $this->raw($t)->refresh_token);
    }

    #[Test]
    public function test_missing_config_key_stores_plaintext()
    {
        config()->set('shopee.encrypt_tokens', null);
        $t = $this->make();
        $this->assertSame('AT', $this->raw($t)->access_token);
        $this->assertSame('AT', $t->fresh()->access_token);
    }

    #[Test]
    public function test_enabled_stores_ciphertext_and_reads_plaintext()
    {
        config()->set('shopee.encrypt_tokens', true);
        $t = $this->make();
        $this->assertNotSame('AT', $this->raw($t)->access_token);
        $this->assertSame('AT', Crypt::decryptString($this->raw($t)->access_token));
        $this->assertSame('AT', $t->fresh()->access_token);
        $this->assertSame('RT', $t->fresh()->refresh_token);
    }

    #[Test]
    public function test_enabled_reads_legacy_plaintext_row()
    {
        $t = $this->make();
        config()->set('shopee.encrypt_tokens', true);
        $this->assertSame('AT', $t->fresh()->access_token);
    }

    #[Test]
    public function test_disabled_after_enabled_still_reads_encrypted_row()
    {
        config()->set('shopee.encrypt_tokens', true);
        $t = $this->make();
        config()->set('shopee.encrypt_tokens', false);
        $this->assertSame('AT', $t->fresh()->access_token);
    }

    #[Test]
    public function test_command_refuses_when_disabled()
    {
        $this->make();
        $this->artisan('shopee:encrypt-tokens')
            ->expectsOutput('Enable shopee.encrypt_tokens first.')
            ->assertExitCode(1);
    }

    #[Test]
    public function test_command_encrypts_plaintext_rows_once()
    {
        $t = $this->make();
        config()->set('shopee.encrypt_tokens', true);

        $this->artisan('shopee:encrypt-tokens')
            ->expectsOutput('Encrypted 2 value(s).')
            ->assertExitCode(0);

        $this->assertNotSame('AT', $this->raw($t)->access_token);
        $this->assertNotSame('RT', $this->raw($t)->refresh_token);

        $this->artisan('shopee:encrypt-tokens')
            ->expectsOutput('Encrypted 0 value(s).')
            ->assertExitCode(0);

        $fresh = $t->fresh();
        $this->assertSame('AT', $fresh->access_token);
        $this->assertSame('RT', $fresh->refresh_token);
    }
}
