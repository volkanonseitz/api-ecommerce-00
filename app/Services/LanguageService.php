<?php

namespace App\Services;

use App\Models\Language;
use App\DTO\LanguageData;

class LanguageService
{
    public function getAll()
    {
        return Language::get();
    }

    public function find($id): Language
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