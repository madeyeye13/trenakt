<div>
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-white/40 mb-1">Management</p>
            <h1 class="text-2xl font-bold">Campaign categories</h1>
        </div>
        <a href="{{ route('admin.categories.create') }}" wire:navigate
            class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2.5 hover:opacity-90 transition">
            New category
        </a>
    </div>

    @if ($categories->isEmpty())
        <x-empty-state icon="briefcase" title="No categories yet"
            description="Create your first campaign category to define pricing and requirement fields.">
            <a href="{{ route('admin.categories.create') }}" wire:navigate
                class="bg-trenakt-primary text-white text-sm font-medium rounded-md px-4 py-2">Create category</a>
        </x-empty-state>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($categories as $category)
                <div class="bg-white dark:bg-trenakt-surface-dark border border-gray-200 dark:border-white/10 rounded-lg p-5">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="text-sm font-semibold">{{ $category->name }}</h3>
                        <span class="text-[10px] uppercase tracking-wide rounded-full px-2 py-0.5
                            {{ $category->is_active ? 'bg-trenakt-success-light text-trenakt-success' : 'bg-gray-100 dark:bg-white/5 text-gray-400' }}">
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 line-clamp-2">{{ $category->description ?: 'No description.' }}</p>
                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 mb-4">
                        <span>₦{{ number_format($category->min_rate) }} - ₦{{ number_format($category->max_rate) }}</span>
                        <span>{{ $category->platform_fee_percentage }}% fee</span>
                    </div>
                    <div class="flex items-center gap-3 pt-3 border-t border-gray-100 dark:border-white/10">
                        <a href="{{ route('admin.categories.edit', $category) }}" wire:navigate class="text-xs font-medium text-trenakt-primary">Edit</a>
                        <button type="button" @click="$dispatch('open-modal', { name: 'delete-category', id: {{ $category->id }} })" class="text-xs font-medium text-trenakt-danger">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <x-modal name="delete-category">
        <div x-data="{ deleteId: null }" x-on:open-modal.window="if ($event.detail.name === 'delete-category') deleteId = $event.detail.id">
            <h3 class="text-lg font-semibold mb-2">Delete this category?</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">This cannot be undone. Categories with existing campaigns cannot be deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" @click="$dispatch('close-modal')" class="text-sm font-medium text-gray-500">Cancel</button>
                <button type="button" @click="$wire.delete(deleteId)" class="bg-trenakt-danger text-white text-sm font-medium rounded-md px-4 py-2">Delete</button>
            </div>
        </div>
    </x-modal>
</div>