<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssetRepair extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'series',
        'asset_id',
        'repair_status',
        'failure_date',
        'completed_date',
        'error_description',
        'actions_performed',
        'repair_cost_total',
        'status',
        'journal_entry_id',
        'created_by',
        'submitted_at',
        'submitted_by',
    ];

    protected $casts = [
        'failure_date' => 'date',
        'completed_date' => 'datetime',
        'submitted_at' => 'datetime',
        'repair_cost_total' => 'decimal:2',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function items()
    {
        return $this->hasMany(AssetRepairItem::class);
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}