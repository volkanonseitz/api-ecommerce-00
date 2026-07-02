<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Models\User;
use App\Modules\Checkout\Actions\VerifyCheckoutAction;
use App\Modules\Checkout\DTO\CheckoutVerifyData;
use App\Modules\Checkout\Http\Requests\CheckoutVerifyRequest;
use App\Modules\Checkout\Http\Resources\CheckoutResource;

final class CheckoutController extends BaseController
{
    public function __construct(private readonly VerifyCheckoutAction $verifyAction) {}

    public function verify(CheckoutVerifyRequest $request): CheckoutResource
    {
        $this->authorize('verify', User::class);

        $data = CheckoutVerifyData::fromRequest($request->validated());
        $result = $this->verifyAction->execute($data, $request->user());

        return new CheckoutResource($result);
    }
}
