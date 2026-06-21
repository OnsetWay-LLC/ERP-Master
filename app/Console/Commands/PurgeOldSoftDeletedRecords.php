<?php

namespace App\Console\Commands;

use App\Models\SalesPerson;
use App\Models\SalesOrder;
use App\Models\SalesInvoice;
use Illuminate\Console\Command;

class PurgeOldSoftDeletedRecords extends Command
{
    protected $signature = 'soft-deletes:purge-old';

    protected $description = 'Permanently delete soft deleted records older than one year';

    public function handle(): int
    {
        $models = [
            SalesPerson::class,
            SalesOrder::class,
            SalesInvoice::class,
            
            // ضيفي هون أي Model يستخدم SoftDeletes
        ];

        foreach ($models as $model) {
            $deletedCount = $model::onlyTrashed()
                ->where('deleted_at', '<=', now()->subYear())
                ->forceDelete();

            $this->info($model . ' deleted: ' . $deletedCount);
        }

        return self::SUCCESS;
    }
}