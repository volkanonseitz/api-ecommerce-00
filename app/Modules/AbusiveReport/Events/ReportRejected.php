<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Events;

use App\Models\AbusiveReport;
use Illuminate\Foundation\Events\Dispatchable;

final class ReportRejected
{
    use Dispatchable;

    public function __construct(public readonly AbusiveReport $report) {}
}
