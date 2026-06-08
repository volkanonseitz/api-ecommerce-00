<?php

namespace App\Services;

use App\Models\Tax;

class TaxService
{
    public function getAll()
    {
        return Tax::all();
    }

    public function find($id): Tax
    {
        return Tax::findOrFail($id);
    }

    public function create(array $data): Tax
    {
        return Tax::create($data);
    }

    public function update($id, array $data): Tax
    {
        $tax = Tax::findOrFail($id);
        $tax->update($data);
        return $tax->fresh();
    }

    public function delete($id): void
    {
        $tax = Tax::findOrFail($id);
        $tax->delete();
    }
}