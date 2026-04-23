<?php

namespace App\Http\Requests;

use App\Models\Task;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'label' => Task::labelForTitle($this->input('title')),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'course_id' => 'required|exists:courses,id',
            'label' => [
                'required',
                'string',
                Rule::in(Task::topicLabels()),
                Rule::unique('tasks', 'label')->where(fn ($query) => $query->where('course_id', $this->input('course_id'))),
            ],
            'title' => ['required', 'string', Rule::in(Task::topicTitles())],
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip|max:10240',
            'start_date' => 'nullable|date_format:Y-m-d\TH:i',
            'deadline' => 'required|date_format:Y-m-d\TH:i|after_or_equal:start_date',
        ];
    }
}
