<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\User\UserProfiles;
use App\Mail\SendEmailVerificationToken;
use App\Mail\SendResetToken;
use App\Models\User\EmailVerificationToken;
use App\Models\User\PasswordResetToken;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password()
    {
        Mail::fake();

        $user = User::factory()->create(['profile' => UserProfiles::Patient]);
        $data = [ 'email' => $user->email ];

        $response = $this->postJson(route('users.sendResetToken'), $data);
        $response->assertOk();

        $passwordResetToken = PasswordResetToken::where('email', $user->email)->first();
        $this->assertNotNull($passwordResetToken);
        $this->assertFalse($passwordResetToken->used);

        Mail::assertSent(SendResetToken::class, function (SendResetToken $mail) use ($user, $passwordResetToken) {
            return str_contains($mail->render(), $passwordResetToken->token)
                && $mail->hasTo($user->email);
        });

        Mail::assertSentCount(1);

        $data = [
            'token' => $passwordResetToken->token,
            'email' => $user->email,
            'password' => 'newPassword',
            'password_confirmation' => 'newPassword',
        ];

        $response = $this->postJson(route('users.resetPassword'), $data);
        $response->assertOk();

        $this->assertTrue(Hash::check('newPassword', $user->fresh()->password));
    }
}
