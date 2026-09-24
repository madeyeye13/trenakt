@php
    $isComplete = $user->hasCompleteParticipantProfile();
@endphp

<div class="max-w-2xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Account</p>
        <h1 class="text-2xl font-bold">Your profile</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Keep this accurate. It's how we match you to tasks you're eligible for.</p>
    </div>

    @if (! $isComplete)
        <div class="flex items-start gap-3 bg-trenakt-warning-light text-trenakt-warning rounded-lg p-4 mb-6">
            <x-icon name="alert-triangle" class="w-5 h-5 shrink-0 mt-0.5" />
            <div>
                <p class="text-sm font-semibold">Complete your profile to see available tasks</p>
                <p class="text-sm mt-0.5">Fill in your name, phone, date of birth, and country below.</p>
            </div>
        </div>
    @endif

    <form wire:submit="save" class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-5">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Full name</label>
            <input wire:model="name" type="text" placeholder="Your real name, matching your bank account"
                class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md px-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
            @error('name') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            <p class="text-xs text-gray-400 dark:text-white/40 mt-1.5">Use your real name as it appears on your bank account. Mismatched names can hold up a withdrawal.</p>
        </div>

        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Phone number</label>
                <div class="relative">
                    <x-icon name="phone" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input wire:model="phone" type="tel" placeholder="080..."
                        class="w-full border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 rounded-md pl-9 pr-3 py-2.5 text-sm focus:outline-none focus:border-trenakt-primary">
                </div>
                @error('phone') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Date of birth</label>
                <x-date-picker model="date_of_birth" :selected="$date_of_birth ?: null" placeholder="Select your date of birth"
                    min="{{ now()->subYears(100)->format('Y-m-d') }}" max="{{ now()->subYears(13)->format('Y-m-d') }}" />
                @error('date_of_birth') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1.5">Country</label>
            <x-select model="country_id" :options="$countries" :selected="$country_id" placeholder="Select your country" />
            @error('country_id') <p class="text-xs text-trenakt-danger mt-1.5">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save profile</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </form>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 mt-6 flex items-start gap-3">
        <div class="w-9 h-9 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center shrink-0">
            <x-icon name="bank" class="w-4.5 h-4.5" />
        </div>
        <div class="min-w-0">
            <p class="text-sm font-medium">Bank account</p>
            @if ($user->bankAccount?->isVerified())
                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">
                    {{ $user->bankAccount->bank_name }} &middot; {{ $user->bankAccount->account_name }} &middot; ****{{ substr($user->bankAccount->account_number, -4) }}
                </p>
            @else
                <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">
                    You'll be asked to add and verify your bank account the first time you request a withdrawal from
                    <a href="{{ route('earnings.index') }}" wire:navigate class="text-trenakt-primary hover:underline">Earnings</a>.
                </p>
            @endif
        </div>
    </div>

    @if ($isParticipant)
        <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 mt-6 flex items-start gap-3">
            <div class="w-9 h-9 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center shrink-0">
                <x-icon name="user" class="w-4.5 h-4.5" />
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium">Refer & earn</p>

                @if ($referralSystemEnabled)
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5 mb-2">Share your link. When someone you referred activates their account, you earn a reward.</p>
                    <div x-data="{
                        copied: false,
                        async copyLink() {
                            const text = '{{ $referralLink }}';
                            try {
                                if (navigator.clipboard && window.isSecureContext) {
                                    await navigator.clipboard.writeText(text);
                                } else {
                                    const el = document.createElement('textarea');
                                    el.value = text;
                                    el.style.position = 'fixed';
                                    el.style.opacity = '0';
                                    document.body.appendChild(el);
                                    el.focus();
                                    el.select();
                                    document.execCommand('copy');
                                    document.body.removeChild(el);
                                }
                            } catch (e) {}
                            this.copied = true;
                            setTimeout(() => this.copied = false, 1500);
                        }
                    }" class="flex items-center gap-2 border border-gray-200 dark:border-white/10 rounded-md px-3 py-2 max-w-sm">
                        <span class="text-xs truncate flex-1 min-w-0">{{ $referralLink }}</span>
                        <button type="button" @click="copyLink()"
                            class="text-xs font-medium text-trenakt-primary shrink-0">
                            <span x-show="!copied">Copy</span>
                            <span x-show="copied" x-cloak>Copied!</span>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-2">Or share your code: <span class="font-mono font-semibold text-trenakt-dark dark:text-white">{{ $referralCode }}</span></p>
                @else
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-0.5">Coming soon.</p>
                @endif
            </div>
        </div>
    @endif

    <div class="lg:hidden bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg mt-6 divide-y divide-gray-100 dark:divide-white/10">
        <div x-data="{
                theme: localStorage.getItem('trenakt-theme') || 'light',
                toggle() {
                    this.theme = this.theme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('trenakt-theme', this.theme);
                    document.documentElement.setAttribute('data-theme', this.theme);
                    window.dispatchEvent(new CustomEvent('trenakt-theme-changed', { detail: this.theme }));
                }
            }" class="flex items-center justify-between gap-3 p-5">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center shrink-0">
                    <x-icon name="sun" class="w-4.5 h-4.5" x-show="theme === 'light'" />
                    <x-icon name="moon" class="w-4.5 h-4.5" x-show="theme === 'dark'" x-cloak />
                </div>
                <p class="text-sm font-medium">Appearance</p>
            </div>
            <button type="button" @click="toggle()" class="text-sm font-medium text-trenakt-primary shrink-0">
                <span x-text="theme === 'dark' ? 'Dark' : 'Light'"></span>
            </button>
        </div>

        <button type="button" @click="$store.logoutConfirm.open = true"
            class="w-full flex items-center gap-3 p-5 text-left">
            <div class="w-9 h-9 rounded-full bg-trenakt-danger/10 text-trenakt-danger flex items-center justify-center shrink-0">
                <x-icon name="logout" class="w-4.5 h-4.5" />
            </div>
            <p class="text-sm font-medium text-trenakt-danger">Log out</p>
        </button>
    </div>
</div>
