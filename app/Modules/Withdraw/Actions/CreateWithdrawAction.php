<?php

declare(strict_types=1);

namespace App\Modules\Withdraw\Actions;

use App\Enums\WithdrawStatus;
use App\Models\Balance;
use App\Models\Withdraw;
use App\Modules\Withdraw\DTO\WithdrawData;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class CreateWithdrawAction
{
    public function execute(WithdrawData $data, Authenticatable $user): Withdraw
    {
        $balance = Balance::where('shop_id', $data->shop_id)->first();
        if (! $balance || $balance->current_balance < $data->amount) {
            throw new BadRequestHttpException(config('notice.INSUFFICIENT_BALANCE'));
        }

        $withdraw = Withdraw::create($data->toArray());
        $withdraw->status = WithdrawStatus::PENDING->value;
        $withdraw->save();

        // Update balance
        $balance->withdrawn_amount += $data->amount;
        $balance->current_balance -= $data->amount;
        $balance->save();

        return $withdraw;
    }
}
