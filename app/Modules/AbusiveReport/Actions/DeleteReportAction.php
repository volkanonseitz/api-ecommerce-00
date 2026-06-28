<?php

declare(strict_types=1);

namespace App\Modules\AbusiveReport\Actions;

use App\Models\AbusiveReport;

class DeleteReportAction
{
    public function execute(AbusiveReport $report): void
    {
        $report->delete();
    }
}
