<div x-data="installPrompt()" x-show="visible" x-cloak
    class="fixed bottom-4 inset-x-4 sm:inset-x-auto sm:right-4 sm:w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-4 z-50">
    <div class="flex items-start gap-3">
        <img src="{{ asset('images/Favicon01.png') }}" alt="Trenakt" class="w-9 h-9 rounded-md shrink-0 object-cover">
        <div class="flex-1">
            <p class="text-sm font-medium text-trenakt-dark">Install Trenakt</p>
            <p class="text-xs text-gray-500 mt-0.5" x-show="!isIOS">Add Trenakt to your home screen for quick access.</p>
            <p class="text-xs text-gray-500 mt-0.5" x-show="isIOS" x-cloak>
                Tap the Share icon, then "Add to Home Screen".
            </p>
        </div>
        <button type="button" @click="dismiss()" class="text-gray-400 hover:text-gray-600 shrink-0">
            <x-icon name="x" class="w-4 h-4" />
        </button>
    </div>
    <button type="button" x-show="!isIOS" @click="install()"
        class="w-full mt-3 bg-trenakt-primary text-white text-sm font-medium rounded-md py-2 hover:opacity-90 transition">
        Install
    </button>
</div>