<?php
namespace App\Models;
 use Illuminate\Database\Eloquent\Model;
 use Illuminate\Database\Eloquent\SoftDeletes;
class FeesTemplate extends Model
{
    use SoftDeletes;

protected $fillable = [
    'company_id',
    'title',
    'type',
    'account_id',
    'fees_rate',
    'amount',
    'is_active',
    'created_by',
];
public function account()
{
    return $this->belongsTo(ChartOfAccount::class);
}
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}
public function company()
{
    return $this->belongsTo(Company::class);
}
}