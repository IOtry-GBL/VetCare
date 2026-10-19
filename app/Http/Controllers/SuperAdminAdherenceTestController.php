<?php

namespace App\Http\Controllers;

use App\Mail\AdherenceNotificationTestMail;
use App\Models\SuperAdminAdherenceTest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class SuperAdminAdherenceTestController extends Controller
{
    public function index(): View
    {
        return view('super-admin.adherence-testing', [
            'recipients' => User::query()
                ->where('role', 'Pet Owner')
                ->where('status', 'Active')
                ->orderBy('full_name')
                ->get(['user_id', 'full_name', 'email']),
            'tests' => SuperAdminAdherenceTest::query()
                ->with('creator')
                ->latest()
                ->limit(20)
                ->get(),
        ]);
    }

    public function send(Request $request)
    {
        $validated = $request->validate([
            'recipient_user_id' => [
                'required',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query
                    ->where('role', 'Pet Owner')
                    ->where('status', 'Active')),
            ],
        ]);

        $recipient = User::query()
            ->where('role', 'Pet Owner')
            ->where('status', 'Active')
            ->findOrFail($validated['recipient_user_id']);

        $token = Str::random(64);
        $test = SuperAdminAdherenceTest::query()->create([
            'id' => (string) Str::uuid(),
            'recipient_user_id' => $recipient->user_id,
            'created_by' => $request->user()->user_id,
            'recipient_name' => $recipient->full_name,
            'recipient_email' => $recipient->email,
            'medication_name' => 'Test medication',
            'dosage' => 'Test dosage',
            'token_hash' => hash('sha256', $token),
            'status' => 'Sending',
            'expires_at' => now()->addHour(),
        ]);

        $confirmationUrl = URL::temporarySignedRoute(
            'adherence-test.show',
            $test->expires_at,
            ['token' => $token]
        );

        try {
            Mail::to($test->recipient_email)->send(new AdherenceNotificationTestMail(
                recipientName: $test->recipient_name,
                medicationName: $test->medication_name,
                dosage: $test->dosage,
                confirmationUrl: $confirmationUrl,
                expiresAt: $test->expires_at,
            ));
        } catch (Throwable $exception) {
            $test->update(['status' => 'Failed']);
            Log::error('Super Admin adherence test email failed.', [
                'test_id' => $test->id,
                'recipient_user_id' => $recipient->user_id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()->route('super-admin.adherence-tests.index')
                ->with('error', 'The test email could not be sent. Check the mail configuration and application logs.');
        }

        $test->update(['status' => 'Sent']);

        return redirect()->route('super-admin.adherence-tests.index')
            ->with('success', 'Adherence test email sent to the selected pet owner.');
    }

    public function show(Request $request, string $token): View
    {
        $test = $this->findTest($token);

        if ($test->status === 'Sent' && $test->expires_at->isPast()) {
            $test->update(['status' => 'Expired']);
            $test->refresh();
        }

        $confirmationUrl = URL::temporarySignedRoute(
            'adherence-test.confirm',
            $test->expires_at,
            ['token' => $token]
        );

        return view('adherence-test.confirm', compact('test', 'confirmationUrl'));
    }

    public function confirm(Request $request, string $token)
    {
        $test = $this->findTest($token);

        if ($test->status === 'Sent' && $test->expires_at->isPast()) {
            $test->update(['status' => 'Expired']);
            $test->refresh();
        }

        if ($test->status === 'Sent' && ! $test->expires_at->isPast()) {
            SuperAdminAdherenceTest::query()
                ->whereKey($test->id)
                ->where('status', 'Sent')
                ->where('expires_at', '>=', now())
                ->update([
                    'status' => 'Confirmed',
                    'confirmed_at' => now(),
                ]);
            $test->refresh();
        }

        return view('adherence-test.confirm', [
            'test' => $test,
            'confirmationUrl' => null,
        ]);
    }

    private function findTest(string $token): SuperAdminAdherenceTest
    {
        return SuperAdminAdherenceTest::query()
            ->where('token_hash', hash('sha256', $token))
            ->firstOrFail();
    }
}
