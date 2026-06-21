<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChartOfAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'parent_id',
        'name_ar',
        'name_en',
        'account_number',
        'root_category',
        'sub_category',
        'account_type',
        'account_level',
        'is_active',
        'is_system',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(ChartOfAccount::class, 'parent_id');
    }
public function childrenRecursive()
{
    return $this->children()->with('childrenRecursive');
}
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function paymentEntriesPaidFrom()
{
    return $this->hasMany(PaymentEntry::class, 'paid_from_account_id');
}

public function paymentEntriesPayable()
{
    return $this->hasMany(PaymentEntry::class, 'payable_account_id');
}
}