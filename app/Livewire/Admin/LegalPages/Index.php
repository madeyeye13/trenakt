<?php

namespace App\Livewire\Admin\LegalPages;

use App\Models\Setting;
use App\Support\DefaultLegalContent;
use Livewire\Component;

class Index extends Component
{
    public string $termsContent = '';
    public string $privacyContent = '';

    public function mount(): void
    {
        $this->termsContent = (string) Setting::get('terms_content', DefaultLegalContent::terms());
        $this->privacyContent = (string) Setting::get('privacy_content', DefaultLegalContent::privacy());
    }

    public function save(): void
    {
        $this->validate([
            'termsContent' => ['required', 'string'],
            'privacyContent' => ['required', 'string'],
        ], [], [
            'termsContent' => 'Terms of Service',
            'privacyContent' => 'Privacy Policy',
        ]);

        Setting::set('terms_content', $this->termsContent);
        Setting::set('privacy_content', $this->privacyContent);

        $this->dispatch('toast', type: 'success', message: 'Legal pages saved.');
    }

    public function render()
    {
        return view('livewire.admin.legal-pages.index')
            ->layout('components.layouts.admin', ['title' => 'Legal pages']);
    }
}
