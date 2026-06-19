<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\TaxCreateRequest;
use App\Http\Requests\TaxUpdateRequest;
use App\Services\TaxService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TaxController extends BaseController
{
    public function __construct(private TaxService $taxService) {}

    public function index()
    {
        $taxes = Cache::rememberForever('taxes_all', function () {
            return $this->taxService->getAll();
        });

        return $this->sendSuccess($taxes, 'Taxes retrieved');
    }

    public function store(TaxCreateRequest $request)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $tax = $this->taxService->create($request->validated());
        Cache::forget('taxes_all');

        return $this->sendSuccess($tax, 'Tax created', 201);
    }

    public function show($id)
    {
        $tax = $this->taxService->find($id);

        return $this->sendSuccess($tax, 'Tax detail');
    }

    public function update(TaxUpdateRequest $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $tax = $this->taxService->update($id, $request->validated());
        Cache::forget('taxes_all');

        return $this->sendSuccess($tax, 'Tax updated');
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (! $user || ! $user->hasPermissionTo(Permission::SUPER_ADMIN->value)) {
            throw new AuthorizationException(config('notice.NOT_AUTHORIZED'));
        }
        $this->taxService->delete($id);
        Cache::forget('taxes_all');

        return $this->sendSuccess(null, 'Tax deleted');
    }
}
