<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\BankAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankAccountTest extends TestCase
{
    use RefreshDatabase;

    protected $host;

    protected function setUp(): void
    {
        parent::setUp();

        $this->host = User::factory()->create([
            'role' => 'host',
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function host_can_create_bank_account()
    {
        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson('/api/v1/bank-accounts', [
                'bank_name' => 'BCR',
                'account_name' => 'John Doe',
                'account_holder_name' => 'John Doe',
                'iban' => 'RO49AAAA1B31007593840000',
                'bic_swift' => 'RNCBROBU',
                'currency' => 'RON',
                'is_default' => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'account' => ['id', 'bank_name', 'account_holder_name', 'is_default'],
            ]);

        $this->assertDatabaseHas('bank_accounts', [
            'user_id' => $this->host->id,
            'bank_name' => 'BCR',
            'is_default' => true,
        ]);
    }

    /** @test */
    public function first_bank_account_is_automatically_default()
    {
        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson('/api/v1/bank-accounts', [
                'bank_name' => 'BCR',
                'account_name' => 'John Doe',
                'account_holder_name' => 'John Doe',
                'iban' => 'RO49AAAA1B31007593840000',
                'bic_swift' => 'RNCBROBU',
                'currency' => 'RON',
            ]);

        $response->assertStatus(201);

        $account = BankAccount::where('user_id', $this->host->id)->first();
        $this->assertTrue($account->is_default);
    }

    /** @test */
    public function setting_account_as_default_unsets_others()
    {
        $account1 = BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'is_default' => true,
        ]);

        $account2 = BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'is_default' => false,
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->putJson("/api/v1/bank-accounts/{$account2->id}", [
                'is_default' => true,
            ]);

        $response->assertStatus(200);

        $account1->refresh();
        $account2->refresh();

        $this->assertFalse($account1->is_default);
        $this->assertTrue($account2->is_default);
    }

    /** @test */
    public function host_can_update_bank_account()
    {
        $account = BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'bank_name' => 'BCR',
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->putJson("/api/v1/bank-accounts/{$account->id}", [
                'bank_name' => 'BRD',
                'bic_swift' => 'BRDEROBU',
            ]);

        $response->assertStatus(200);

        $account->refresh();
        $this->assertEquals('BRD', $account->bank_name);
        $this->assertEquals('BRDEROBU', $account->bic_swift);
    }

    /** @test */
    public function host_can_delete_bank_account()
    {
        $account = BankAccount::factory()->create([
            'user_id' => $this->host->id,
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->deleteJson("/api/v1/bank-accounts/{$account->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('bank_accounts', [
            'id' => $account->id,
        ]);
    }

    /** @test */
    public function deleting_default_account_sets_another_as_default()
    {
        $account1 = BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'is_default' => true,
        ]);

        $account2 = BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'is_default' => false,
        ]);

        $this->actingAs($this->host, 'sanctum')
            ->deleteJson("/api/v1/bank-accounts/{$account1->id}");

        $account2->refresh();
        $this->assertTrue($account2->is_default);
    }

    /** @test */
    public function host_cannot_modify_other_host_accounts()
    {
        $otherHost = User::factory()->create(['role' => 'host']);
        $account = BankAccount::factory()->create([
            'user_id' => $otherHost->id,
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->putJson("/api/v1/bank-accounts/{$account->id}", [
                'bank_name' => 'Updated',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function validates_required_fields()
    {
        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson('/api/v1/bank-accounts', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['bank_name', 'account_name', 'account_holder_name', 'iban', 'bic_swift']);
    }

    /** @test */
    public function can_get_bank_details_for_payment()
    {
        $guest = User::factory()->create(['role' => 'guest']);
        
        BankAccount::factory()->create([
            'user_id' => $this->host->id,
            'bank_name' => 'BCR',
            'account_name' => 'John Doe',
            'account_holder_name' => 'John Doe',
            'is_default' => true,
            'is_active' => true,
        ]);

        $property = \App\Models\Property::factory()->create([
            'user_id' => $this->host->id,
        ]);

        $booking = \App\Models\Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
        ]);

        $payment = \App\Models\Payment::factory()->create([
            'user_id' => $guest->id,
            'booking_id' => $booking->id,
            'amount' => 1500.00,
            'currency' => 'RON',
        ]);

        $response = $this->actingAs($guest, 'sanctum')
            ->getJson("/api/v1/payments/{$payment->id}/bank-details");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'account' => ['bank_name', 'account_holder_name', 'iban'],
                'payment',
                'instructions',
            ])
            ->assertJson([
                'account' => [
                    'bank_name' => 'BCR',
                ],
                'instructions' => [
                    'amount' => 1500.00,
                    'currency' => 'RON',
                ],
            ]);
    }
}
