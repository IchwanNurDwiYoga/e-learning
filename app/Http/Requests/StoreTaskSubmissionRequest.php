<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskSubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
      * @return array<string, array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => 'nullable|string',
            'task_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip,rar|max:10240',
            'product_file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip,rar|max:10240',
            // Legacy single-file key to avoid breaking old clients.
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,zip,rar|max:10240',
            'confirm_final_submission' => 'nullable|boolean',
        ];
    }
}
