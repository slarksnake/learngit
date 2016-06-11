<?php
namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class PurchaseManage
 * @package App\Models\V1
 *
 * 错误码 3000~3999
 */
class OrdersManage extends BaseModel
{
    protected $table = 'orders_material';
    public $timestamps = false;

    public static function createOrders($data = [])
    {
        ApplyManage::isApplyExist($data['apply_id'], $state = 12);
        self::isOrdersExistByApply($data['apply_id']);
        $data['create_at'] = time();

        $name = $data['sup_name'];
        // 供应商名称唯一
        $where = "is_del='0' AND sup_name = '$name'";
        $result = DB::table('supplier')->whereRaw($where)->select('id')->first();
        if (!$result) {
            $sup = array_slice($data, 0, 9);
        }
        $info = ApplyManage::selectMaterialApply($data['apply_id'])->toArray();
        foreach ($info as $key => $value){
            if (in_array($key, array('number', 'name', 'category_id', 'unit', 'specification', 'project_id', 'purchase')) ){
                $data[$key] = $value;
            }
        }
        
        $id = DB::transaction(function () use ($sup, $data) {
            Supplier::createSupplier($sup);
            $id = self::insertGetId($data);
            ApplyManage::changeState($data['apply_id'], 13);
            return $id;
        });

        return $id;
    }

    public static function selectOrdersById($id)
    {
        $data = self::where([
            ['orders_id', $id],
            ['is_del', '0']
        ])->select('*')->first();

        if (! $data) {
            throw new Exception('该订单不存在@404', 2003);
        }

        return $data;
    }

    private static function isOrdersExistByApply($applyId)
    {
        $result = self::where([
            ['apply_id', $applyId],
            ['is_del', '0']
        ])->select('orders_id')->first();
        if ($result) {
            throw new Exception('该订单已被生成，请勿重复操作@404', 3007);
        }

    }
}