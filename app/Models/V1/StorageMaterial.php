<?php
namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class StorageMaterial
 * @package App\Models\V1
 *
 * 错误码 7000~7099
 */
class StorageMaterial extends BaseModel
{
    protected $table = 'storage_material';
    public $timestamps = false;

    public static function createStorage($data = [])
    {
        self::isStorageExistByOrders($data['orders_id']);

        //订单号信息
        $res = OrdersManage::selectOrdersById($data['orders_id'])->toArray();
        foreach ($res as $key => $value) {
            if (in_array($key, array('number', 'name', 'category_id', 'unit', 'specification', 'project_id'))) {
                $material[$key] = $value;
            }
            if (in_array($key, array('price', 'apply_id'))) {
                $data[$key] = $value;
            }
        }

        $name = $res['sup_name'];
        // 供应商信息
        $result = Supplier::selectSupplierByName($name);
        $material['supplier_id'] = $result['id'];

        $data['create_at'] = time();
        $material['amount'] = $data['amount'];
        $id = DB::transaction(function () use ($material, $data) {
            $id = Material::addMaterial($material);
            $data['material_id'] = $id;
            return self::insertGetId($data);
        });

        return $id;
    }

    public static function listStorage($p, $n, $name)
    {
        $searchCondition = [['storage_material.is_del', 0]];

        if ($name !== '') {
            $searchCondition[] = ['material.name', 'like', $name.'%'];
        }

        $data = self::where($searchCondition)
            ->join('material', 'material.m_id', '=', 'storage_material.material_id')
            ->join('category_material', 'category_material.id', '=', 'material.category_id')
            ->join('category_project', 'category_project.id', '=', 'material.project_id')
            ->paginate($n, [
                'sto_id as stoId',
                'material.number',
                'storage_material.url',
                'category_material.name as categoryName',
                'specification',
                'storage_material.amount',
                'material.unit',
                'category_project.name as projectName',
                'user_id as userId',
                'storage_material.create_at as createAt',
                'storage_material.is_invoice as invoice',
                'storage_material.is_reimburse as reimburse',
                'storage_material.is_storage as storage'
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function updateStorage($id, $data = [])
    {
        $result = self::where([
            ['sto_id', $id],
            ['is_del', '0']
        ])->select('sto_id')->first();

        if (! $result) {
            throw new Exception('无库存记录@404', 7007);
        }

        $data['updated_at'] = time();
        self::where('sto_id', $id)->update($data);
    }

    public static function listReimburse($p, $n, $start, $end, $project)
    {
        $searchCondition = [['storage_material.is_del', 0],['storage_material.is_reimburse',1]];

        if ($start !== '') {
            $searchCondition[] = ['storage_material.update_at', '>', $start];
        }

        if ($end !== '') {
            $searchCondition[] = ['storage_material.update_at', '<', $end];
        }

        if ($project !== '') {
            $searchCondition[] = ['material.project_id', $project];
        }

        $data = self::where($searchCondition)
            ->join('material', 'material.m_id', '=', 'storage_material.material_id')
            ->join('category_material', 'category_material.id', '=', 'material.category_id')
            ->join('category_project', 'category_project.id', '=', 'material.project_id')
            ->paginate($n, [
                'sto_id as stoId',
                'material.number',
                'category_material.name as categoryName',
                'specification',
                'storage_material.amount',
                'material.unit',
                'storage_material.price',
                'category_project.name as projectName',
                'storage_material.update_at as time',
                'storage_material.is_invoice as invoice',
            ], 'p', $p);

        return self::getPageData($data);

    }

    private static function isStorageExistByOrders($ordersId)
    {
        $result = self::where([
            ['orders_id', $ordersId],
            ['is_del', '0']
        ])->select('sto_id')->first();

        if ($result) {
            throw new Exception('该订单已被生成，请勿重复操作@404', 3007);
        }

    }
}