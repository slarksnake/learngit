<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class MaterialCate
 * @package App\Models\V1
 *
 * 错误码 3000~3999
 */
class Material extends BaseModel
{
    protected $table = 'material';
    public $timestamps = false;

    public static function createMaterial($data=[])
    {
        $result = self::where([
            ['name', $data['name']],
            ['is_del', 0]
        ])->select('m_id')->first();
        
        if ($result) {
            return  self::updateMaterial($result['m_id'], $data);
        }

        $data['create_at'] = time();

        return self::insertGetId($data);
    }

    public static function selectMaterialById($id, $fields=['m_id', 'name', 'number', 'url', 'specification', 'productionDate', 'amount', 'unit'])
    {
        $data = self::where([
            ['m_id', $id],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该材料不存在@404', 3011);
        }

        $data = $data->toArray();
        
        $categoryId = $data['category_id'];

        $categoryData = MaterialCate::selectCategoryById($categoryId);
         
        $data['categoryName'] = $categoryData['name'];

        return $data;
    }

    public static function selectMaterialByName($name, $fields=['*'])
    {
        $data = self::where([
            ['name', $name],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该材料不存在@404', 3011);
        }

        return $data->toArray();
    }

    public static function updateMaterial($id, $data=[])
    {
        if ($data['name']){
            self::selectMaterialByName($data['name']);
        }

        $res = self::selectMaterialById($id);

        $new['updated_at'] = time();
        $new['amount'] = $res['amount'] ;
        self::where('id', $id)->update($new);

        return $id;
    }
    
    public static function addMaterial($id, $amount)
    {
        self::selectMaterialById($id);
        self::increment('amount', $amount, array('m_id'=>$id));
        return true;
    }
    
    public static function reduceMaterial($id, $amount)
    {
        self::selectMaterialById($id);
        self::decrement('amount', $amount, array('m_id'=>$id));
        return true;        
    }

    public static function listMaterial($p, $n, $name, $supplier)
    {
        $searchCondition = [['material.is_del', 0]];

        if ($name !== '') {
            $searchCondition[] = ['material.name', 'like', $name.'%'];
        }

        if ($supplier !== '') {
            $searchCondition[] = ['material.project_id', $supplier];
        }

        $data = self::where($searchCondition)
            ->join('material', 'material.m_id', '=', 'supplier.id')
            ->join('category_material', 'category_material.id', '=', 'material.category_id')
            ->join('category_project', 'category_project.id', '=', 'material.project_id')
            ->paginate($n, [
                'sto_id as stoId',
                'material.number',
                'category_material.name as categoryName',
                'specification',
                'material.amount',
                'material.unit',
                'category_project.name as projectName',
            ], 'p', $p);

        return self::getPageData($data);
    }


}