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
            'requirementFields.*.label' => ['required', 'string', 'max:255'],
            'requirementFields.*.type' => ['required', 'in:text,textarea,file,url,number'],
        ]);

        DB::transaction(function () {
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