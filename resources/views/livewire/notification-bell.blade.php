<div class="relative" x-data="{ open: false }" @click.outside="open = false"
    x-init="
        if (window.Echo) {
            window.Echo.private('App.Models.User.{{ auth()->id() }}')
                .notification(() => { $wire.$refresh() });
        }
    "
    wire:poll.visible.20s>

    <button type="button" @click="open = !open"
        class="relative text-gray-500 dark:text-white/60 hover:text-trenakt-dark dark:hover:text-white transition" title="Notifications">
        <x-icon name="bell" class="w-5.5 h-5.5" />

        @if ($unreadCount > 0)
            <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-trenakt-accent text-white text-[10px] font-semibold flex items-center justify-center">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div x-show="open" x-cloak x-transition
        class="fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-16 sm:top-auto sm:mt-3 sm:w-96 bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg shadow-lg overflow-hidden z-30">

        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-white/10">
            <p class="text-sm font-semibold">Notifications</p>
            @if ($unreadCount > 0)
                <button type="button" wire:click="markAllAsRead" class="text-xs font-medium text-trenakt-primary hover:underline">
                    Mark all read
                </button>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-white/10 scrollbar-brand">
            @forelse ($notifications as $notification)
                @php
                    $isUnread = is_null($notification->read_at);
                    $type = $notification->data['type'] ?? null;
                    $typeIcon = match ($type) {
                        'campaign_approved', 'task_approved', 'withdrawal_processed', 'referral_reward_earned', 'account_activated' => 'check-circle',
                        'campaign_rejected', 'task_rejected', 'withdrawal_failed' => 'x-circle',
                        'campaign_pending_review', 'campaign_submitted', 'task_submission_received' => 'briefcase',
                        'wallet_funded' => 'wallet',
                        'wallet_funding_failed' => 'alert-triangle',
                        default => 'bell',
                    };
                    $typeClass = match ($type) {
                        'campaign_approved', 'wallet_funded', 'task_approved', 'withdrawal_processed', 'referral_reward_earned', 'account_activated' => 'bg-trenakt-success-light text-trenakt-success',
                        'campaign_rejected', 'wallet_funding_failed', 'task_rejected', 'withdrawal_failed' => 'bg-trenakt-danger/10 text-trenakt-danger',
                        default => 'bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary',
                    };
                    $url = $this->urlFor($notification);
                @endphp

                @if ($url)
                    <a href="{{ $url }}" wire:click="markAsRead('{{ $notification->id }}')"
                        class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-white/5 transition {{ $isUnread ? 'bg-trenakt-primary-light/40 dark:bg-white/[0.03]' : '' }}">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $typeClass }}">
                            <x-icon name="{{ $typeIcon }}" class="w-4 h-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="text-sm block {{ $isUnread ? 'font-medium text-trenakt-dark dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-white/40 mt-0.5 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @if ($isUnread)
                            <span class="w-2 h-2 rounded-full bg-trenakt-accent shrink-0 mt-2"></span>
                        @endif
                    </a>
                @else
                    <button type="button" wire:click="markAsRead('{{ $notification->id }}')"
                        class="w-full flex items-start gap-3 px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-white/5 transition {{ $isUnread ? 'bg-trenakt-primary-light/40 dark:bg-white/[0.03]' : '' }}">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $typeClass }}">
                            <x-icon name="{{ $typeIcon }}" class="w-4 h-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="text-sm block {{ $isUnread ? 'font-medium text-trenakt-dark dark:text-white' : 'text-gray-500 dark:text-gray-400' }}">
                                {{ $notification->data['message'] ?? 'Notification' }}
                            </span>
                            <span class="text-xs text-gray-400 dark:text-white/40 mt-0.5 block">{{ $notification->created_at->diffForHumans() }}</span>
                        </span>
                        @if ($isUnread)
                            <span class="w-2 h-2 rounded-full bg-trenakt-accent shrink-0 mt-2"></span>
                        @endif
                    </button>
                @endif
            @empty
                <div class="flex flex-col items-center text-center py-10 px-4">
                    <div class="w-10 h-10 rounded-full bg-trenakt-primary-light dark:bg-white/5 text-trenakt-primary flex items-center justify-center mb-3">
                        <x-icon name="bell" class="w-4.5 h-4.5" />
                    </div>
                    <p class="text-sm font-medium">No notifications yet</p>
                    <p class="text-xs text-gray-400 dark:text-white/40 mt-1">We'll let you know when something needs your attention.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
