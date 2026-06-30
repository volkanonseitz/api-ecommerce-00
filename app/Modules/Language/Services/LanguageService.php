<?php

declare(strict_types=1);

namespace App\Modules\Language\Services;

use App\Models\Language;
use App\Modules\Language\DTO\LanguageData;

class LanguageService
{
    public function getAll()
    {
        return Language::all();
    }

    public function find(int $id): Language
    {
        return Language::findOrFail($id);
    }

    public function create(LanguageData $data): Language
    {
        return Language::create([
            'language_name' => $data->language_name,
            'language_code' => $data->language_code,
            'flag' => $data->flag,
        ]);
    }

    public function update(Language $language, LanguageData $data): Language
    {
        $language->update([
            'language_name' => $data->language_name,
            'language_code' => $data->language_code,
            'flag' => $data->flag,
        ]);

        return $language->fresh();
    }

    public function delete(Language $language): void
    {
        $language->delete();
    }
}
