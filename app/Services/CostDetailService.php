<?php

namespace App\Services;

use App\Models\Cost;
use App\Models\CostDetail;

/**
 * Lưu các dòng hàng (cost_detail) của 1 bill.
 * Dùng chung cho form Cost (CostController) và API nhập nhanh (CostApiController).
 */
class CostDetailService
{
    /**
     * - Dòng có id -> cập nhật, dòng mới -> thêm, dòng không còn trong danh sách -> xóa.
     * - detail_total = tổng tiền các dòng; detail_check = 1 khi tổng dòng khác tổng bill (vẫn cho lưu).
     * @param int $source 1 = nhập tay trên form, 2 = nhập qua API (Claude đọc bill)
     */
    public static function saveRows(Cost $cost, $rows, $userId, $source = 1)
    {
        $rows = is_array($rows) ? $rows : [];
        $existing = $cost->details()->get()->keyBy('id');
        $keepIds = [];
        $sum = 0;
        $count = 0;
        foreach($rows as $row){
            $ingredientId = !empty($row['ingredient_id']) ? (int) $row['ingredient_id'] : null;
            $quantity = self::toNumber($row['quantity'] ?? null, true);
            $unitPrice = self::toNumber($row['unit_price'] ?? null);
            $amount = self::toNumber($row['amount'] ?? null);
            $notes = isset($row['notes']) ? trim($row['notes']) : null;
            $unit = isset($row['unit']) ? trim($row['unit']) : null;
            if(!$ingredientId && $quantity === null && $amount === null && !$notes){
                continue; // dòng trống
            }
            if($amount === null && $quantity !== null && $unitPrice !== null){
                $amount = (int) round($quantity * $unitPrice);
            }
            $data = [
                'ingredient_id' => $ingredientId,
                'quantity' => $quantity,
                'unit' => $unit !== '' ? $unit : null,
                'unit_price' => $unitPrice,
                'amount' => $amount,
                'notes' => $notes !== '' && $notes !== null ? mb_substr($notes, 0, 255) : null,
                'need_review' => !empty($row['need_review']) ? 1 : 0,
            ];
            $id = !empty($row['id']) ? (int) $row['id'] : null;
            if($id && $existing->has($id)){
                $existing[$id]->update($data);
                $keepIds[] = $id;
            }else{
                $data['cost_id'] = $cost->id;
                $data['source'] = $source;
                $data['created_user'] = $userId;
                CostDetail::create($data);
            }
            $sum += (int) $amount;
            $count++;
        }
        $deleteIds = $existing->keys()->diff($keepIds)->all();
        if($deleteIds){
            CostDetail::whereIn('id', $deleteIds)->delete();
        }
        $cost->detail_total = $count > 0 ? $sum : null;
        $cost->detail_check = ($count > 0 && $sum != (int) $cost->total_money) ? 1 : 0;
        $cost->save();
    }

    /**
    * "1,5" -> 1.5 (số lượng) ; "200,000" / "200.000" -> 200000 (tiền)
    */
    public static function toNumber($value, $decimal = false)
    {
        if($value === null) return null;
        $value = trim((string) $value);
        if($value === '') return null;
        if($decimal){
            $value = str_replace(' ', '', $value);
            if(strpos($value, ',') !== false && strpos($value, '.') === false){
                $value = str_replace(',', '.', $value);
            }else{
                $value = str_replace(',', '', $value);
            }
            return is_numeric($value) ? (float) $value : null;
        }
        $value = preg_replace('/[^0-9\-]/', '', $value);
        return $value === '' || $value === '-' ? null : (int) $value;
    }
}
