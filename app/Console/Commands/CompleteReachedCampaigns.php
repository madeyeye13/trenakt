<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CompleteReachedCampaigns extends Command
{
    protected $signature = 'campaigns:complete-reached';

    protected $description = 'Marks approved campaigns that have hit their target participant count as completed, and deletes stored post-content media for ones using the "post on your own page" mode.';

    /**
     * 'completed' has been a valid campaigns.status value since the table
     * was created (see its migration) and is already used by the
     * business-facing campaign list filters, but nothing ever actually
     * set it - this command is the first thing that does.
     *
     * Media cleanup only ever touches post_content_media (the flyer/
     * image/video a business supplies for "post on your own page" mode,
     * see CampaignCategory.supports_post_modes) - it never touches the
     * general-purpose CampaignRequirementAnswer file uploads, which are a
     * separate, unrelated storage path used by other category types.
     *
     * Deletion happens the moment a campaign reaches its target, same as
     * asked for. A participant who already started but hasn't submitted
     * yet loses access to view the flyer once the campaign fills - a
     * known tradeoff of doing this immediately rather than after a grace
     * period, worth revisiting if it turns out to matter in practice.
     */
    public function handle(): int
    {
        $reached = Campaign::query()
            ->where('status', 'approved')
            ->whereRaw(
                '(select count(*) from campaign_submissions cs where cs.campaign_id = campaigns.id and cs.status != ?) >= campaigns.target_participants',
                ['rejected']
            )
            ->get();

        if ($reached->isEmpty()) {
            $this->info('No campaigns have reached their target since the last run.');
            return self::SUCCESS;
        }

        foreach ($reached as $campaign) {
            $campaign->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
            ])->save();

            if ($campaign->post_content_media) {
                try {
                    Storage::disk('public')->delete($campaign->post_content_media);
                } catch (\Throwable $e) {
                    Log::warning('Could not delete post-content media for completed campaign: ' . $e->getMessage(), [
                        'campaign_id' => $campaign->id,
                    ]);
                }

                $campaign->forceFill(['post_content_media' => null])->save();
            }

            $this->info("Campaign #{$campaign->id} ({$campaign->title}) marked completed.");
        }

        return self::SUCCESS;
    }
}
