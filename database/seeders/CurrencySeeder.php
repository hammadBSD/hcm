<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Currency;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            ['name' => 'Pakistani Rupee', 'code' => 'PKR', 'symbol' => '₨', 'exchange_rate' => 1.0000, 'is_base_currency' => true, 'status' => 'active'],
            ['name' => 'US Dollar', 'code' => 'USD', 'symbol' => '$', 'exchange_rate' => 280.00, 'is_base_currency' => false, 'status' => 'active'],
            ['name' => 'UAE Dirham', 'code' => 'AED', 'symbol' => 'د.إ', 'exchange_rate' => 76.20, 'is_base_currency' => false, 'status' => 'active'],
            ['name' => 'Saudi Riyal', 'code' => 'SAR', 'symbol' => '﷼', 'exchange_rate' => 74.67, 'is_base_currency' => false, 'status' => 'active'],
            ['name' => 'British Pound', 'code' => 'GBP', 'symbol' => '£', 'exchange_rate' => 350.00, 'is_base_currency' => false, 'status' => 'active'],
            ['name' => 'Euro', 'code' => 'EUR', 'symbol' => '€', 'exchange_rate' => 305.00, 'is_base_currency' => false, 'status' => 'active'],
        ];

        // If base currency exists, set others to not base
        foreach ($currencies as $currency) {
            if ($currency['is_base_currency']) {
                Currency::where('is_base_currency', true)->update(['is_base_currency' => false]);
            }
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
