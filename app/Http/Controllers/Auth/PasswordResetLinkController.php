<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class PasswordResetLinkController extends Controller
{
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        if (in_array(config('mail.default'), ['array', 'log'], true)) {
            return back()->with(
                'mail_error',
                'Email delivery is not configured. Ask an administrator to enable SMTP email settings, then try again.'
            );
        }

        try {
            Password::sendResetLink($request->only('email'));
        } catch (TransportExceptionInterface|\RuntimeException $exception) {
            Log::warning('Password reset email delivery failed.', [
                'exception' => $exception::class,
            ]);

            return back()->with(
                'mail_error',
                'We could not send the reset email right now. Please try again later or contact the clinic.'
            );
        }

        return back()->with(
            'status',
            'If an account exists for that email address, a password reset link has been sent.'
        );
    }
}
