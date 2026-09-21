<x-layouts.guest title="Reset password">
    <livewire:auth.reset-password-form :token="$request->route('token')" />
</x-layouts.guest>