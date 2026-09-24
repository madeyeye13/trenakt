<?php

namespace App\Livewire\Admin\Settings;

use App\Models\RejectionReason;
use App\Models\Setting;
use Livewire\Component;

class Index extends Component
{
    public string $payoutDay = 'thursday';
    public bool $withdrawalAlwaysOpen = false;
    public string $verificationMode = 'human';

    // Write-only: never populated from the stored value, so the real key is
    // never round-tripped back into the browser. A blank save leaves
    // whatever's already configured untouched - see save().
    public string $openaiApiKey = '';
    public bool $openaiKeyConfigured = false;
    public string $aiAutoApproveThreshold = '0.85';
    public string $aiAutoRejectThreshold = '0.85';
    // String, not int: x-select always writes back a JS string via
    // $wire.set, including '' for the "never auto-reject" option - a plain
    // string property accepts that safely, where a typed ?int property would
    // throw trying to coerce '' to an int.
    public ?string $aiRejectionReasonId = null;

    public bool $activationFeeEnabled = false;
    public string $activationFeeAmount = '';
    public bool $activationFeeGrandfatherExisting = true;
    public string $referralRewardAmount = '';

    public function mount(): void
    {
        $this->payoutDay = (string) Setting::get('payout_day', 'thursday');
        $this->withdrawalAlwaysOpen = (bool) Setting::get('withdrawal_always_open', false);
        $this->verificationMode = (string) Setting::get('verification_mode', 'human');
        $this->openaiKeyConfigured = (bool) Setting::getEncrypted('openai_api_key');
        $this->aiAutoApproveThreshold = (string) Setting::get('ai_auto_approve_threshold', '0.85');
        $this->aiAutoRejectThreshold = (string) Setting::get('ai_auto_reject_threshold', '0.85');
        $this->aiRejectionReasonId = Setting::get('ai_rejection_reason_id') ?: null;
        $this->activationFeeEnabled = (bool) Setting::get('activation_fee_enabled', false);
        $this->activationFeeAmount = (string) Setting::get('activation_fee_amount', '');
        $this->activationFeeGrandfatherExisting = (bool) Setting::get('activation_fee_grandfather_existing', true);
        $this->referralRewardAmount = (string) Setting::get('referral_reward_amount', '');
    }

    public function save(): void
    {
        // x-select's "never auto-reject" option writes '' rather than null;
        // normalise it here so 'nullable' below actually applies (Laravel's
        // nullable rule checks for real null, not an empty string).
        if ($this->aiRejectionReasonId === '') {
            $this->aiRejectionReasonId = null;
        }

        $this->validate([
            'payoutDay' => ['required', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'],
            'verificationMode' => ['required', 'in:human,ai'],
            'openaiApiKey' => ['nullable', 'string', 'max:255'],
            'aiAutoApproveThreshold' => ['required', 'numeric', 'min:0', 'max:1'],
            'aiAutoRejectThreshold' => ['required', 'numeric', 'min:0', 'max:1'],
            'aiRejectionReasonId' => ['nullable', 'exists:rejection_reasons,id'],
            'activationFeeAmount' => [$this->activationFeeEnabled ? 'required' : 'nullable', 'numeric', 'min:0'],
            'referralRewardAmount' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'openaiApiKey' => 'OpenAI API key',
            'aiAutoApproveThreshold' => 'auto-approve confidence threshold',
            'aiAutoRejectThreshold' => 'auto-reject confidence threshold',
            'aiRejectionReasonId' => 'AI rejection reason',
            'activationFeeAmount' => 'activation fee amount',
            'referralRewardAmount' => 'referral reward amount',
        ]);

        Setting::set('payout_day', $this->payoutDay);
        // The value column is plain text, so store an explicit '1'/'0' rather
        // than a PHP bool (Postgres rejects binding a boolean into a text
        // column). PHP treats the string '0' as falsy, so reads still work
        // with a plain truthiness check.
        Setting::set('withdrawal_always_open', $this->withdrawalAlwaysOpen ? '1' : '0');

        Setting::set('verification_mode', $this->verificationMode);

        // A blank field means "leave the existing key alone" - the input is
        // never pre-filled with the real value, so there's no way to tell
        // "admin cleared it" from "admin didn't touch it" apart from that.
        if ($this->openaiApiKey !== '') {
            Setting::setEncrypted('openai_api_key', $this->openaiApiKey);
            $this->openaiApiKey = '';
            $this->openaiKeyConfigured = true;
        }

        Setting::set('ai_auto_approve_threshold', $this->aiAutoApproveThreshold);
        Setting::set('ai_auto_reject_threshold', $this->aiAutoRejectThreshold);
        // Auto-reject is opt-in: with no reason chosen here, AiVerifier can
        // still auto-approve or leave a submission for human review, but it
        // will never call TaskService::reject() on its own. See
        // VerifySubmissionWithAi::rejectionReason().
        Setting::set('ai_rejection_reason_id', $this->aiRejectionReasonId ?: '');

        Setting::set('activation_fee_enabled', $this->activationFeeEnabled ? '1' : '0');
        Setting::set('activation_fee_amount', $this->activationFeeAmount !== '' ? $this->activationFeeAmount : '0');
        Setting::set('activation_fee_grandfather_existing', $this->activationFeeGrandfatherExisting ? '1' : '0');
        Setting::set('referral_reward_amount', $this->referralRewardAmount !== '' ? $this->referralRewardAmount : '0');

        // The very first time the fee is switched on, remember the moment -
        // this is the cutoff ActivationFeeService uses to decide who counts
        // as an "existing" participant when grandfathering is on. It's only
        // ever set once; toggling the fee off and back on later never moves
        // it, so grandfathered participants stay grandfathered.
        if ($this->activationFeeEnabled && ! Setting::get('activation_fee_required_from')) {
            Setting::set('activation_fee_required_from', now()->toDateTimeString());
        }

        $this->dispatch('toast', type: 'success', message: 'Settings saved.');
    }

    public function render()
    {
        return view('livewire.admin.settings.index', [
            'rejectionReasons' => RejectionReason::active()->get(),
        ])->layout('components.layouts.admin', ['title' => 'Settings']);
    }
}
