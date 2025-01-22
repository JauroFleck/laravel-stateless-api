<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\User\UserProfiles;
use App\Mail\SendEmailVerificationToken;
use App\Models\User\EmailVerificationToken;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification()
    {
        Mail::fake();

        Sanctum::actingAs($user = User::factory()->create(['profile' => UserProfiles::Patient]));
        $this->assertNull($user->email_verified_at);

        $response = $this->postJson(route('users.sendEmailVerification'));
        $response->assertOk();

        $emailVerificationToken = EmailVerificationToken::where('user_id', $user->id)->first();
        $this->assertNotNull($emailVerificationToken);

        Mail::assertSent(SendEmailVerificationToken::class, function (SendEmailVerificationToken $mail) use ($user, $emailVerificationToken) {
            return str_contains($mail->render(), $emailVerificationToken->token)
                && $mail->hasTo($user->email);
        });

        Mail::assertSentCount(1);

        $data = [
            'token' => $emailVerificationToken->token,
        ];

        $response = $this->postJson(route('users.verifyEmail'), $data);
        $response->assertOk();

        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}
