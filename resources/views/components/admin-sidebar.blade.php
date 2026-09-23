@php
    $pendingCampaignsCount = \App\Models\Campaign::where('status', 'pending_review')->count();
    $pendingSubmissionsCount = \App\Models\CampaignSubmission::where('status', 'submitted')->count();
    $pendingWithdrawalsCount = \App\Models\WithdrawalRequest::where('status', 'pending')->count();
    $pendingActivationPaymentsCount = \App\Models\ActivationPayment::where('status', 'pending')->count();
@endphp

<aside
    x-cloak
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    {{ $attributes->merge(['class' => 'fixed inset-y-0 left-0 z-50 w-64 flex flex-col bg-white dark:bg-trenakt-surface-dark border-r border-gray-200 dark:border-white/10 h-screen transform transition-transform duration-200 ease-in-out lg:translate-x-0 lg:sticky lg:top-0 lg:z-auto']) }}>
    <div class="px-5 py-6 flex items-center justify-between border-b border-gray-200 dark:border-white/10">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-lg bg-trenakt-dark/5 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-trenakt-accent flex items-center justify-center">
                <x-icon name="shield" class="w-4.5 h-4.5" />
            </div>
            <div>
                <p class="text-sm font-bold leading-none text-trenakt-dark dark:text-white">Trenakt</p>
                <p class="text-[10px] uppercase tracking-wide text-gray-400 dark:text-white/40 mt-0.5">Admin console</p>
            </div>
        </div>

        <button type="button" @click="sidebarOpen = false" class="lg:hidden text-gray-400 dark:text-white/40 hover:text-trenakt-dark dark:hover:text-white transition">
            <x-icon name="x" class="w-5 h-5" />
        </button>
    </div>

    <div class="px-3 py-4 flex-1 overflow-y-auto">
        <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-2">Menu</p>

        <nav class="space-y-1">
            <a href="{{ route('admin.dashboard') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.dashboard') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <x-icon name="grid" class="w-4.5 h-4.5" />
                Dashboard
            </a>

            <a href="{{ route('admin.campaigns.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.campaigns.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <span class="flex items-center gap-3">
                    <x-icon name="briefcase" class="w-4.5 h-4.5" />
                    Campaigns
                </span>
                @if ($pendingCampaignsCount > 0)
                    <span class="text-[10px] font-semibold bg-trenakt-accent text-white rounded-full px-2 py-0.5">{{ $pendingCampaignsCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.categories.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.categories.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <x-icon name="briefcase" class="w-4.5 h-4.5" />
                Categories
            </a>

            <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mt-5 mb-2">Earning</p>

            <a href="{{ route('admin.submissions.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.submissions.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <span class="flex items-center gap-3">
                    <x-icon name="check-circle" class="w-4.5 h-4.5" />
                    Submissions
                </span>
                @if ($pendingSubmissionsCount > 0)
                    <span class="text-[10px] font-semibold bg-trenakt-accent text-white rounded-full px-2 py-0.5">{{ $pendingSubmissionsCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.rejection-reasons.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.rejection-reasons.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <x-icon name="x-circle" class="w-4.5 h-4.5" />
                Rejection reasons
            </a>

            <a href="{{ route('admin.withdrawals.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.withdrawals.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <span class="flex items-center gap-3">
                    <x-icon name="bank" class="w-4.5 h-4.5" />
                    Withdrawals
                </span>
                @if ($pendingWithdrawalsCount > 0)
                    <span class="text-[10px] font-semibold bg-trenakt-accent text-white rounded-full px-2 py-0.5">{{ $pendingWithdrawalsCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.activation-payments.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center justify-between gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.activation-payments.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <span class="flex items-center gap-3">
                    <x-icon name="shield" class="w-4.5 h-4.5" />
                    Activation payments
                </span>
                @if ($pendingActivationPaymentsCount > 0)
                    <span class="text-[10px] font-semibold bg-trenakt-accent text-white rounded-full px-2 py-0.5">{{ $pendingActivationPaymentsCount }}</span>
                @endif
            </a>

            <a href="{{ route('admin.countries.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition mt-5
                    {{ request()->routeIs('admin.countries.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <x-icon name="switch" class="w-4.5 h-4.5" />
                Countries
            </a>

            <a href="{{ route('admin.settings.index') }}" wire:navigate @click="sidebarOpen = false"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                    {{ request()->routeIs('admin.settings.*') ? 'bg-trenakt-accent/15 text-trenakt-accent' : 'text-gray-500 dark:text-white/60 hover:bg-gray-50 dark:hover:bg-white/5' }}">
                <x-icon name="grid" class="w-4.5 h-4.5" />
                Settings
            </a>
        </nav>
    </div>

    <div class="border-t border-gray-200 dark:border-white/10 p-3">
        <div class="flex items-center gap-3 px-2 py-2">
            <div class="w-9 h-9 rounded-full bg-trenakt-accent text-white flex items-center justify-center text-sm font-semibold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-trenakt-dark dark:text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 dark:text-white/40">{{ auth()->user()->hasRole('super_admin') ? 'Super admin' : 'Admin' }}</p>
            </div>
            <button type="button" @click="$dispatch('open-modal', { name: 'confirm-logout' })"
                class="text-gray-400 dark:text-white/40 hover:text-trenakt-dark dark:hover:text-white transition" title="Log out">
                <x-icon name="logout" class="w-4.5 h-4.5" />
            </button>
        </div>
    </div>
</aside>

<x-modal name="confirm-logout" maxWidth="sm">
    <h3 class="text-lg font-semibold mb-2">Log out?</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">You'll need to sign back in to get back to the admin console.</p>
    <div class="flex justify-end gap-3">
        <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2 hover:opacity-90 transition">
                Yes, log out
            </button>
        </form>
    </div>
</x-modal>
