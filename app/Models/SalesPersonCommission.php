<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesPersonCommission extends Model
{
    protected $fillable = [
        'company_id',
        'sales_person_id',
        'sales_person_target_id',
        'from_date',
        'to_date',
        'target_amount',
        'total_period_target',
        'total_actual_sales',
        'achievement_percentage',
        'commission_rate',
        'commission_amount',
        'journal_entry_id',
        'employee_allowance_id',
        'status',
        'posted_at',
        'posted_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'posted_at' => 'datetime',
    ];

    public function salesPerson()
    {
        return $this->belongsTo(SalesPerson::class);
    }

    public function target()
    {
        return $this->belongsTo(SalesPersonTarget::class, 'sales_person_target_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function employeeAllowance()
    {
        return $this->belongsTo(EmployeeAllowance::class);
    }

    public function postedBy()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}