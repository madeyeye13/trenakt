<?php

namespace App\Services\Verification;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

/**
 * Calls OpenAI's Chat Completions API with a strict JSON-schema response
 * format, so the result is parsed as structured data rather than scraped
 * out of free-form text. The key is read from Setting::getEncrypted() first
 * (Super Admin-configured, see Settings\Index) and only falls back to
 * config('services.openai.key') - an optional .env value for local/dev use
 * before anything's been configured in the database.
 */
class OpenAiEvaluator implements AiEvaluator
{
    protected const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    public function evaluate(SubmissionEvaluationRequest $request): AiEvaluationResult
    {
        $apiKey = Setting::getEncrypted('openai_api_key') ?: config('services.openai.key');

        if (! $apiKey) {
            throw new AiEvaluationException('No OpenAI API key is configured.');
        }

        $model = Setting::get('openai_model') ?: config('services.openai.model', 'gpt-4o-mini');
        $timeout = (int) config('services.openai.timeout', 20);

        try {
            $response = Http::withToken($apiKey)
                ->timeout($timeout)
                ->post(self::ENDPOINT, [
                    'model' => $model,
                    'temperature' => 0,
                    'messages' => [
                        ['role' => 'system', 'content' => $this->systemPrompt()],
                        ['role' => 'user', 'content' => $this->buildUserContent($request)],
                    ],
                    'response_format' => [
                        'type' => 'json_schema',
                        'json_schema' => [
                            'name' => 'submission_verification',
                            'strict' => true,
                            'schema' => $this->responseSchema(),
                        ],
                    ],
                ]);
        } catch (\Throwable $e) {
            throw new AiEvaluationException('OpenAI request failed: ' . $e->getMessage(), previous: $e);
        }

        if ($response->failed()) {
            throw new AiEvaluationException('OpenAI returned HTTP ' . $response->status() . ': ' . $response->body());
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        if (! $content || ! is_string($content)) {
            throw new AiEvaluationException('OpenAI response had no message content.');
        }

        $decoded = json_decode($content, true);

        if (! is_array($decoded) || ! $this->isValidShape($decoded)) {
            throw new AiEvaluationException('OpenAI response did not match the expected structured format.');
        }

        return new AiEvaluationResult(
            verdict: $decoded['verdict'],
            confidence: (float) $decoded['confidence'],
            checks: $decoded['checks'] ?? [],
            reasons: $decoded['reasons'] ?? [],
            evidenceConsidered: $decoded['evidence_considered'] ?? [],
            raw: (array) $response->json(),
        );
    }

    protected function isValidShape(array $decoded): bool
    {
        return isset($decoded['verdict'], $decoded['confidence'])
            && in_array($decoded['verdict'], ['approved', 'rejected', 'needs_review'], true)
            && is_numeric($decoded['confidence']);
    }

    protected function systemPrompt(): string
    {
        return <<<'PROMPT'
You are a verification assistant for a micro-task platform. Participants complete tasks defined by a business (app/website testing, market research, publicity, or other task types the platform doesn't hardcode) and submit evidence. Your job is to judge whether the submitted evidence reasonably satisfies the business's stated instructions and requirements - nothing more.

Rules:
- Judge only against the instructions, requirements, and evidence given to you in this message. Never assume a requirement that wasn't stated.
- A negative, critical, or unexpected opinion is never a reason to reject. Only flag evidence that is missing, irrelevant, nonsensical, empty, or that plainly does not demonstrate what was asked for.
- If you are not genuinely confident, say so with a lower confidence score and prefer "needs_review" over guessing either way.
- Never fabricate detail about an image or link you were not given actual content for.
- Automated notes you're given (e.g. a possible duplicate-answer flag) are context to weigh, not conclusions to repeat as fact.
- Respond only with the structured JSON the API call requires.
PROMPT;
    }

    /** @return array<int, array{type: string, text?: string, image_url?: array}> */
    protected function buildUserContent(SubmissionEvaluationRequest $request): array
    {
        $lines = [];
        $lines[] = "Campaign: {$request->campaignTitle}";

        if ($request->campaignDescription) {
            $lines[] = "Description: {$request->campaignDescription}";
        }

        if ($request->steps !== []) {
            $lines[] = 'Instructions the business gave the participant:';
            foreach (array_values($request->steps) as $i => $step) {
                $lines[] = ($i + 1) . ". {$step}";
            }
        }

        if ($request->businessProvidedInfo !== []) {
            $lines[] = 'Information the business provided for this campaign:';
            foreach ($request->businessProvidedInfo as $label => $value) {
                $lines[] = "- {$label}: {$value}";
            }
        }

        if ($request->advisoryNotes !== []) {
            $lines[] = 'Automated notes to weigh (not conclusive on their own):';
            foreach ($request->advisoryNotes as $note) {
                $lines[] = "- {$note}";
            }
        }

        $lines[] = "Participant's submitted evidence:";

        $content = [['type' => 'text', 'text' => implode("\n", $lines)]];

        foreach ($request->evidenceItems as $item) {
            $desc = "Field: {$item['label']} (type: {$item['type']}, required: " . ($item['is_required'] ? 'yes' : 'no') . ')';

            if (isset($item['text_value'])) {
                $desc .= "\nSubmitted value: {$item['text_value']}";
            } elseif (isset($item['note'])) {
                $desc .= "\n{$item['note']}";
            }

            $content[] = ['type' => 'text', 'text' => $desc];

            if (isset($item['image_data_uri'])) {
                $content[] = ['type' => 'image_url', 'image_url' => ['url' => $item['image_data_uri']]];
            }
        }

        $content[] = [
            'type' => 'text',
            'text' => 'Evaluate strictly against the instructions and requirements above and respond only in the required structured format.',
        ];

        return $content;
    }

    protected function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'verdict' => ['type' => 'string', 'enum' => ['approved', 'rejected', 'needs_review']],
                'confidence' => ['type' => 'number'],
                'checks' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'passed' => ['type' => 'boolean'],
                            'detail' => ['type' => 'string'],
                        ],
                        'required' => ['name', 'passed', 'detail'],
                        'additionalProperties' => false,
                    ],
                ],
                'reasons' => ['type' => 'array', 'items' => ['type' => 'string']],
                'evidence_considered' => ['type' => 'array', 'items' => ['type' => 'string']],
            ],
            'required' => ['verdict', 'confidence', 'checks', 'reasons', 'evidence_considered'],
            'additionalProperties' => false,
        ];
    }
}
