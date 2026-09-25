<?php

namespace App\Livewire\Admin\Categories;

use App\Models\CampaignCategory;
use App\Models\CategoryRequirementField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class Form extends Component
{
    public ?int $categoryId = null;
    public string $name = '';
    public string $description = '';
    public float $min_rate = 500;
    public float $max_rate = 5000;
    public float $platform_fee_percentage = 10;
    public int $min_participants = 1;
    public ?int $max_participants = null;
    public bool $is_active = true;
    public array $requirementFields = [];

    /**
     * When on, campaigns in this category get the "reshare vs post on
     * your own page" choice and a platform multi-select at creation - see
     * the migration that adds this column. platform_bonus_amount is the
     * flat NGN amount added to the participant rate for each platform a
     * business selects beyond the first.
     */
    public bool $supports_post_modes = false;
    public float $platform_bonus_amount = 0;

    /**
     * When on, a submission's reward is held instead of paid the moment
     * it's approved - see TaskService::approve() and
     * SubmissionMonitoringService. monitoring_duration_value/_unit are
     * form-only helpers so the admin can type "24 hours" or "30 minutes"
     * instead of doing the minutes math themselves; save() converts
     * whichever they picked into the plain monitoring_minutes integer the
     * database actually stores, so the hold length is never hardcoded to
     * one unit.
     */
    public bool $requires_monitoring = false;
    public int $monitoring_duration_value = 24;
    public string $monitoring_duration_unit = 'hours';

    public function mount(?CampaignCategory $category = null): void
    {
        if ($category) {
            $this->categoryId = $category->id;
            $this->name = $category->name;
            $this->description = $category->description ?? '';
            $this->min_rate = (float) $category->min_rate;
            $this->max_rate = (float) $category->max_rate;
            $this->platform_fee_percentage = (float) $category->platform_fee_percentage;
            $this->min_participants = $category->min_participants;
            $this->max_participants = $category->max_participants;
            $this->is_active = $category->is_active;
            $this->supports_post_modes = $category->supports_post_modes;
            $this->platform_bonus_amount = (float) $category->platform_bonus_amount;
            $this->requires_monitoring = $category->requires_monitoring;

            if ($category->monitoring_minutes) {
                // Show it in whichever unit divides evenly, so a category
                // saved as "24 hours" doesn't come back showing "1440
                // minutes" on the next edit.
                if ($category->monitoring_minutes % 60 === 0) {
                    $this->monitoring_duration_value = (int) ($category->monitoring_minutes / 60);
                    $this->monitoring_duration_unit = 'hours';
                } else {
                    $this->monitoring_duration_value = $category->monitoring_minutes;
                    $this->monitoring_duration_unit = 'minutes';
                }
            }

            $this->requirementFields = $category->requirementFields()->orderBy('sort_order')->get()
                ->map(fn ($field) => [
                    'id' => $field->id,
                    'label' => $field->label,
                    'field_key' => $field->field_key,
                    'type' => $field->type,
                    'fills_for' => $field->fills_for,
                    'is_required' => $field->is_required,
                ])->toArray();
        }

        if (empty($this->requirementFields)) {
            $this->addField();
        }
    }

    public function addField(): void
    {
        $this->requirementFields[] = [
            'id' => null,
            'label' => '',
            'field_key' => '',
            'type' => 'text',
            'fills_for' => 'participant',
            'is_required' => true,
        ];
    }

    public function removeField(int $index): void
    {
        unset($this->requirementFields[$index]);
        $this->requirementFields = array_values($this->requirementFields);
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'min_rate' => ['required', 'numeric', 'min:0'],
            'max_rate' => ['required', 'numeric', 'gt:min_rate'],
            'platform_fee_percentage' => ['required', 'numeric', 'min:0', 'max:100'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'gte:min_participants'],
            'platform_bonus_amount' => ['required', 'numeric', 'min:0'],
            'monitoring_duration_value' => ['required_if:requires_monitoring,true', 'nullable', 'integer', 'min:1'],
            'monitoring_duration_unit' => ['required_if:requires_monitoring,true', 'in:minutes,hours'],
            'requirementFields.*.label' => ['required', 'string', 'max:255'],
            'requirementFields.*.type' => ['required', 'in:text,textarea,file,url,number'],
        ]);

        $monitoringMinutes = $this->requires_monitoring
            ? ($this->monitoring_duration_unit === 'hours' ? $this->monitoring_duration_value * 60 : $this->monitoring_duration_value)
            : null;

        DB::transaction(function () use ($monitoringMinutes) {
            $category = CampaignCategory::updateOrCreate(
                ['id' => $this->categoryId],
                [
                    'name' => $this->name,
                    'slug' => $this->categoryId
                        ? CampaignCategory::find($this->categoryId)->slug
                        : Str::slug($this->name) . '-' . Str::random(4),
                    'description' => $this->description,
                    'min_rate' => $this->min_rate,
                    'max_rate' => $this->max_rate,
                    'min_participants' => $this->min_participants,
                    'max_participants' => $this->max_participants,
                    'platform_fee_percentage' => $this->platform_fee_percentage,
                    'is_active' => $this->is_active,
                    'supports_post_modes' => $this->supports_post_modes,
                    'platform_bonus_amount' => $this->supports_post_modes ? $this->platform_bonus_amount : 0,
                    'requires_monitoring' => $this->supports_post_modes && $this->requires_monitoring,
                    'monitoring_minutes' => $this->supports_post_modes ? $monitoringMinutes : null,
                ]
            );

            $submittedIds = collect($this->requirementFields)->pluck('id')->filter()->all();

            CategoryRequirementField::where('campaign_category_id', $category->id)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            foreach ($this->requirementFields as $index => $field) {
                $requirementField = CategoryRequirementField::find($field['id']) ?? new CategoryRequirementField();
                $requirementField->forceFill([
                    'campaign_category_id' => $category->id,
                    'label' => $field['label'],
                    'field_key' => Str::slug($field['label'], '_'),
                    'type' => $field['type'],
                    'fills_for' => $field['fills_for'] ?? 'participant',
                    'is_required' => $field['is_required'],
                    'sort_order' => $index,
                ])->save();
            }
        });

        $this->dispatch('toast', type: 'success', message: $this->categoryId ? 'Category updated.' : 'Category created.');
        $this->redirect(route('admin.categories.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.categories.form', [
            'fieldTypes' => [
                'text' => 'Short text',
                'textarea' => 'Long text',
                'file' => 'File upload',
                'url' => 'Link',
                'number' => 'Number',
            ],
        ])->layout('components.layouts.admin', ['title' => $this->categoryId ? 'Edit category' : 'New category']);
    }
}
