<?php

namespace App\Livewire\Reports;

use App\Services\CampaignAnalyticsService;
use Livewire\Component;

class Index extends Component
{
    public function exportCsv(CampaignAnalyticsService $analytics)
    {
        $rows = $analytics->campaignsReport(auth()->user());

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Campaign', 'Category', 'Status', 'Submissions', 'Approved', 'Fill rate %', 'Spent (NGN)', 'Created']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['title'],
                    $row['category'],
                    ucfirst(str_replace('_', ' ', $row['status'])),
                    $row['submissions'],
                    $row['approved'],
                    $row['fill_rate'],
                    $row['spent'],
                    $row['created_at']->format('Y-m-d'),
                ]);
            }

            fclose($handle);
        }, 'trenakt-campaign-report-' . now()->format('Y-m-d') . '.csv');
    }

    public function render(CampaignAnalyticsService $analytics)
    {
        $user = auth()->user();

        return view('livewire.reports.index', [
            'spendOverTime' => $analytics->spendOverTime($user),
            'spendByCategory' => $analytics->spendByCategory($user),
            'campaigns' => $analytics->campaignsReport($user),
        ])->layout('components.layouts.app', ['title' => 'Reports']);
    }
}
