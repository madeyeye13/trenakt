<div class="max-w-4xl">
    <div class="mb-6">
        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/30 mb-1">Platform</p>
        <h1 class="text-2xl font-bold">Legal pages</h1>
        <p class="text-sm text-gray-500 dark:text-white/50 mt-1">
            Edit the content shown on your public Terms of Service and Privacy Policy pages. Separate paragraphs with a blank line, and start a section with a number and a period, like "1. What Trenakt is", to have it stand out on the page.
        </p>
    </div>

    <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5 space-y-6">
        <div>
            <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-semibold">Terms of Service</label>
                <a href="{{ route('terms') }}" target="_blank" class="text-xs text-trenakt-accent hover:underline">View live page</a>
            </div>
            <textarea wire:model="termsContent" rows="14"
                class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2.5 text-sm leading-relaxed focus:outline-none focus:border-trenakt-accent resize-y"></textarea>
            @error('termsContent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="pt-2 border-t border-gray-100 dark:border-white/10">
            <div class="flex items-center justify-between mb-1">
                <label class="text-sm font-semibold">Privacy Policy</label>
                <a href="{{ route('privacy') }}" target="_blank" class="text-xs text-trenakt-accent hover:underline">View live page</a>
            </div>
            <textarea wire:model="privacyContent" rows="14"
                class="w-full mt-1 border border-gray-300 dark:border-white/10 dark:bg-white/5 rounded-md px-3 py-2.5 text-sm leading-relaxed focus:outline-none focus:border-trenakt-accent resize-y"></textarea>
            @error('privacyContent') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="button" wire:click="save" wire:loading.attr="disabled" wire:target="save"
                class="bg-trenakt-accent text-white text-sm font-medium rounded-md px-5 py-2.5 hover:opacity-90 transition disabled:opacity-60">
                <span wire:loading.remove wire:target="save">Save legal pages</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>
        </div>
    </div>
</div>
