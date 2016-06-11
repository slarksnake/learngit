<?php
namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

Class Supplier extends BaseModel
{
    protected $table = 'supplier';
    public $timestamps = false;

    public static function createSupplier($data = [])
    {
        self::isSupplierNameUsed($data);

        $data['create_at'] = time();

        return self::insertGetId($data);

    }

    public static function deleteSupplier($id)
    {
        self::isSupplierUsed($id);

        $data = self::where([
            ['id', $id],
            ['is_del', '0']
        ])->select('id')->first();

        if (!$data) {
            throw new Exception('该供应商不存在@404', 2011);
        }

        self::where('id', $id)->update([
            'is_del' => '1',
            'update_at' => time()
        ]);

    }

    public static function listSupplier($p, $n, $name)
    {
        $searchCondition = [['supplier.is_del', '0']];
        if ($name !== '') {
            $searchCondition[] = ['supplier.sup_name', 'like', $name.'%'];
        }

        $data = self::where($searchCondition)
            ->paginate($n, [
                'sup_name',
                'contacts',
                'phone',
                'url',
                'address',
                'account',
                'bank',
                'branch',
                'card',
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function selectSupplierById($id)
    {
        $data = self::where([
            ['id', $id],
            ['is_del', '0']
        ])->select('*')->first();

        if (! $data) {
            throw new Exception('该供应商不存在@404', 2003);
        }

        return $data;
    }

    public static function selectSupplierByName($supName)
    {
        $data = self::where([
            ['sup_name', $supName],
            ['is_del', '0']
        ])->select('*')->first();

        if (! $data) {
            throw new Exception('该供应商不存在@404', 2003);
        }

        return $data;
    }

    /**
     * 预留修改功能
     * @param $id
     * @param array $data

    public static function updateSupplier($id, $data = [])
     * {
     *
     * }*/

    public static function isSupplierNameUsed($data, $id = null)
    {
        $name = $data['sup_name'];
        // 供应商名称唯一
        $where = "is_del='0' AND sup_name = '$name'";

        if ($id) {
            $where .= " AND id<>$id";
        }
        $result = self::whereRaw($where)->select('id')->first();
        if ($result) {
            throw new Exception('供应商名称必须唯一@409', 2001);
        }
    }

    private static function isSupplierUsed($supplierId)
    {
        $result = DB::table('material')->where([
            ['supplier_id', $supplierId],
            ['is_del', '0']
        ])->select('id')->first();
        if ($result) {
            throw new Exception('该供应商已被引用,禁止修改,删除', 2013);
        }
    }


}

