<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class MaterialApply
 * @package App\Models\V1
 *
 * 错误码 3000~3999
 */
class MaterialApply extends BaseModel
{
    protected $table = 'apply_material';
    public $timestamps = false;

    public static function createMaterialApply($data = [])
    {
        $result = MaterialCate::selectCategoryById($data['category_id'], 'id');
        if (! $result) {
            throw new Exception('材料类别不存在@404', 2005);
        }

        $result = ProjectCate::selectCategoryById($data['project_id'], 'id');
        if (! $result) {
            throw new Exception(' 项目类别不存在@404', 2005);
        }

        $data['create_at'] = time();
        $data['state'] = 0;
        
        return self::insertGetId($data);
    }

    public static function listCategory($p, $n)
    {
        $searchCondition = [['apply_material.is_del', '0']];
        $data = self::where($searchCondition)
            ->join('category_material', 'category_material.id', '=', 'apply_material.category_id')
            ->orderBy('apply_material.is_emergency', 'desc')
            ->paginate($n, [
                'am_id',
                'number',
                'category_material.name',

            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function cancel($id)
    {
        $result = self::where([
            ['am_id', $id],
            ['state', 0]
        ])->select('id')->first();
        if (!$result) {
            throw new Exception('该申请不存在@404', 3007);
        }


        self::where('am_id', $id)->update([
            'is_del' => 1,
            'updated_at' => time(),
        ]);

        return true;
    }

    public static function changeState($id, $state)
    {
        $result = self::where([
            ['am_id', $id],
            ['state', $state]
        ])->select('am_id')->first();
        if (!$result) {
            throw new Exception('该申请不存在@404', 3007);
        }

        self::where('am_id', $id)->update([
            'state' => $state,
            'updated_at' => time(),
        ]);

        return true;

    }


}