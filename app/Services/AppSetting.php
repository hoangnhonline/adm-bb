<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/** Đọc / ghi cài đặt trong bảng settings (name, value), có giá trị mặc định từ config/bombay.php */
class AppSetting
{
    protected static $cache = null;

    public static function get($name, $default = null)
    {
        if (self::$cache === null) {
            try { self::$cache = DB::table('settings')->pluck('value', 'name')->all(); } catch (\Throwable $e) { self::$cache = []; }
        }
        if (array_key_exists($name, self::$cache) && self::$cache[$name] !== '') return self::$cache[$name];
        return $default !== null ? $default : config('bombay.' . $name);
    }

    public static function set($name, $value)
    {
        $now = date('Y-m-d H:i:s');
        if (DB::table('settings')->where('name', $name)->exists()) {
            DB::table('settings')->where('name', $name)->update(['value' => (string) $value, 'updated_at' => $now]);
        } else {
            DB::table('settings')->insert(['name' => $name, 'value' => (string) $value, 'created_at' => $now, 'updated_at' => $now]);
        }
        self::$cache = null;
    }

    public static function vatRate()
    {
        return (float) config('bombay.vat_rate', 8);
    }

    /** Chi nhánh không tính VAT (toàn bộ bill đồng bộ từ Sapo) */
    public static function noVatBranches()
    {
        return array_map('intval', (array) config('bombay.no_vat_branches', []));
    }

    /** Bill không tính VAT: tick "KHÔNG VAT" hoặc thuộc chi nhánh không VAT */
    public static function isNoVat($noVat, $branchId = null)
    {
        return (bool) $noVat || ($branchId !== null && in_array((int) $branchId, self::noVatBranches(), true));
    }

    /** VAT của 1 bill: (tổng − giảm giá) × thuế suất, trừ bill không VAT */
    public static function vatOf($totalPrice, $discount, $noVat, $branchId = null)
    {
        if (self::isNoVat($noVat, $branchId)) return 0;
        return round(max(0, (float) $totalPrice - (float) $discount) * self::vatRate() / 100);
    }
}
