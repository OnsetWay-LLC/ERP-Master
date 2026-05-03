<?php

namespace App\Constants;

class AccountTypes
{
    public const CASH = 'cash';
    public const BANK = 'bank';
    public const RECEIVABLE = 'receivable';
    public const STOCK = 'stock';
    public const FIXED_ASSET = 'fixed_asset';
    public const ACCUMULATED_DEPRECIATION = 'accumulated_depreciation';

    public const PAYABLE = 'payable';
    public const TAX = 'tax';

    public const EQUITY = 'equity';

    public const DIRECT_INCOME = 'direct_income';
    public const INDIRECT_INCOME = 'indirect_income';

    public const DIRECT_EXPENSE = 'direct_expense';
    public const INDIRECT_EXPENSE = 'indirect_expense';
    public const COST_OF_GOODS_SOLD = 'cost_of_goods_sold';
    public const DEPRECIATION = 'depreciation';

    public static function values(): array
    {
        return [
            self::CASH,
            self::BANK,
            self::RECEIVABLE,
            self::STOCK,
            self::FIXED_ASSET,
            self::ACCUMULATED_DEPRECIATION,
            self::PAYABLE,
            self::TAX,
            self::EQUITY,
            self::DIRECT_INCOME,
            self::INDIRECT_INCOME,
            self::DIRECT_EXPENSE,
            self::INDIRECT_EXPENSE,
            self::COST_OF_GOODS_SOLD,
            self::DEPRECIATION,
        ];
    }
}