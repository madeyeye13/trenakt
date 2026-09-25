<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Best-effort check of whether a social post URL is still publicly live,
 * used by SubmissionMonitoringService to decide whether a held reward can
 * be released.
 *
 * Only TikTok and Twitter/X expose a public, no-auth way to check this -
 * their oEmbed endpoints return an error once a post is deleted or made
 * private. Instagram and Facebook don't: Meta's oEmbed needs an approved
 * app and access token this platform doesn't have, and scraping the page
 * itself is unreliable and against their terms of service. So those (and
 * any other host) fall back to a plain HEAD request and are only ever
 * flagged 'removed' on an explicit 404 - anything else, including a
 * network failure, timeout, or a page that loads but can't be positively
 * confirmed, comes back 'inconclusive' on purpose. This check gates real
 * money leaving or staying out of someone's wallet, so a false 'removed'
 * verdict is far worse than occasionally missing a real deletion.
 */
class PostAvailabilityChecker
{
    /**
     * @return array{status: 'live'|'removed'|'inconclusive', detail: string}
     */
    public function check(string $url): array
    {
        $host = parse_url($url, PHP_URL_HOST) ?: '';

        try {
            if (str_contains($host, 'tiktok.com')) {
                return $this->viaOembed('https://www.tiktok.com/oembed?url=' . urlencode($url), 'TikTok');
            }

            if (str_contains($host, 'twitter.com') || str_contains($host, 'x.com')) {
                return $this->viaOembed('https://publish.twitter.com/oembed?url=' . urlencode($url) . '&omit_script=true', 'Twitter/X');
            }

            // Instagram, Facebook, and anything else we don't have a
            // reliable no-auth check for.
            return $this->viaHeadRequest($url);
        } catch (\Throwable $e) {
            Log::warning('Post availability check errored: ' . $e->getMessage(), ['url' => $url]);

            return ['status' => 'inconclusive', 'detail' => 'Could not check the link right now.'];
        }
    }

    protected function viaOembed(string $endpoint, string $platform): array
    {
        $response = Http::timeout(8)->get($endpoint);

        if ($response->successful() && $response->json('html')) {
            return ['status' => 'live', 'detail' => $platform . ' confirms the post is still up.'];
        }

        if ($response->status() === 404) {
            return ['status' => 'removed', 'detail' => $platform . ' reports this post no longer exists or was made private.'];
        }

        return ['status' => 'inconclusive', 'detail' => $platform . ' check returned an unexpected response.'];
    }

    protected function viaHeadRequest(string $url): array
    {
        $response = Http::timeout(8)->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (compatible; TrenaktLinkCheck/1.0)',
        ])->head($url);

        if ($response->status() === 404) {
            return ['status' => 'removed', 'detail' => 'The link returned a "page not found" response.'];
        }

        return ['status' => 'inconclusive', 'detail' => 'This platform can\'t be checked automatically with full confidence; the link responded but its content could not be verified.'];
    }
}
