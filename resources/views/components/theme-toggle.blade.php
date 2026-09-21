<div x-data="{
        theme: localStorage.getItem('trenakt-theme') || 'light',
        toggle() {
            this.theme = this.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('trenakt-theme', this.theme);
            document.documentElement.setAttribute('data-theme', this.theme);
        }
    }">
    <button type="button" @click="toggle()" class="text-gray-400 hover:text-trenakt-dark dark:hover:text-white transition" title="Toggle theme">
        <x-icon name="sun" class="w-4.5 h-4.5" x-show="theme === 'light'" />
        <x-icon name="moon" class="w-4.5 h-4.5" x-show="theme === 'dark'" x-cloak />
    </button>
</div>