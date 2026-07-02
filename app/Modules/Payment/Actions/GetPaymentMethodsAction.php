<?php

declare(strict_types=1);

namespace App\Modules\Payment\Actions;

use App\Models\PaymentMethod;
use App\Modules\Payment\Services\PaymentMethodQueryService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

final class GetPaymentMethodsAction
{
    public function __construct(
        private readonly PaymentMethodQueryService $queryService,
    ) {}

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function execute(Authenticatable $user, ?string $gateway = null): Collection
    {
        if ($gateway) {
            return $this->queryService->getUserPaymentMethodsByGateway($user, $gateway);
        }

        return $this->queryService->getUserPaymentMethods($user);
    }
}
