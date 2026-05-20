<?php

namespace App\Constants;

class AccountTypes
{
    public const BANK = 'bank';
    public const CASH = 'cash';
    public const RECEIVABLE = 'receivable';
    public const STOCK = 'stock';
    public const STOCK_ADJUSTMENT = 'stock_adjustment';
    public const FIXED_ASSET = 'fixed_asset';
    public const CAPITAL_WORK_IN_PROGRESS = 'capital_work_in_progress';
    public const ACCUMULATED_DEPRECIATION = 'accumulated_depreciation';
    public const ASSET_RECOVERY = 'asset_recovery';
    public const INVESTMENT = 'investment';
    public const TEMPORARY = 'temporary';
    public const INPUT_TAX = 'input_tax';

    public const PAYABLE = 'payable';
    public const CURRENT_LIABILITY = 'current_liability';
    public const OUTPUT_TAX = 'output_tax';
    public const STOCK_RECEIVED_NOT_BILLED = 'stock_received_not_billed';
    public const SERVICE_RECEIVED_NOT_BILLED = 'service_received_not_billed';
    public const LIABILITY = 'liability';

    public const CAPITAL = 'capital';
    public const EQUITY = 'equity';
    public const DIVIDENDS_PAID = 'dividends_paid';

    public const DIRECT_INCOME = 'direct_income';
    public const INDIRECT_INCOME = 'indirect_income';

    public const COGS = 'cogs';
    public const DIRECT_EXPENSE = 'direct_expense';
    public const EXPENSES_INCLUDED_IN_VALUATION = 'expenses_included_in_valuation';
    public const INDIRECT_EXPENSE = 'indirect_expense';
    public const DEPRECIATION = 'depreciation';
    public const CHARGEABLE = 'chargeable';
    public const ROUND_OFF = 'round_off';
    public const ROUND_OFF_OPENING = 'round_off_opening';

    public const OTHER = 'other';

    public static function values(): array
    {
        return [
            self::BANK,
            self::CASH,
            self::RECEIVABLE,
            self::STOCK,
            self::STOCK_ADJUSTMENT,
            self::FIXED_ASSET,
            self::CAPITAL_WORK_IN_PROGRESS,
            self::ACCUMULATED_DEPRECIATION,
            self::ASSET_RECOVERY,
            self::INVESTMENT,
            self::TEMPORARY,
            self::INPUT_TAX,

            self::PAYABLE,
            self::CURRENT_LIABILITY,
            self::OUTPUT_TAX,
            self::STOCK_RECEIVED_NOT_BILLED,
            self::SERVICE_RECEIVED_NOT_BILLED,
            self::LIABILITY,

            self::CAPITAL,
            self::EQUITY,
            self::DIVIDENDS_PAID,

            self::DIRECT_INCOME,
            self::INDIRECT_INCOME,

            self::COGS,
            self::DIRECT_EXPENSE,
            self::EXPENSES_INCLUDED_IN_VALUATION,
            self::INDIRECT_EXPENSE,
            self::DEPRECIATION,
            self::CHARGEABLE,
            self::ROUND_OFF,
            self::ROUND_OFF_OPENING,

            self::OTHER,
        ];
    }
}


























