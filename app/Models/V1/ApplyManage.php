<?php
namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class ApplyManage
 * @package App\Models\V1
 *
 * 错误码 3000~3999
 */
class ApplyManage extends BaseModel
{
    protected $table = 'apply_material';
    public $timestamps = false;

    public static function createMaterialApply($data = [])
    {
        $result = MaterialCate::selectCategoryById($data['category_id'], 'id');
        if (!$result) {
            throw new Exception('材料类别不存在@404', 2005);
        }

        $result = ProjectCate::selectCategoryById($data['project_id'], 'id');
        if (!$result) {
            throw new Exception(' 项目类别不存在@404', 2005);
        }

        $number = $data['number'];
        $where = "is_del='0' AND number = $number";
        $result = self::whereRaw($where)->select('am_id')->first();
        if ($result) {
            throw new Exception('编号必须唯一@409', 2001);
        }

        $data['create_at'] = time();
        $data['state'] = $data['type'] == 1 ? 10 : 0;

        return self::insertGetId($data);
    }

    public static function listMaterialApply($p, $n, $name, $userId)
    {
        $searchCondition = [['apply_material.is_del', '0']];        
        if ($name !== '') {
            $searchCondition[] = ['apply_material.name', 'like', $name.'%'];
        }
        if ($userId !== '') {
            $searchCondition[] = ['apply_material.user_id', $userId];
        }
        
        $data = self::where($searchCondition)
            ->join('category_material', 'category_material.id', '=', 'apply_material.category_id')
            ->join('category_project', 'category_project.id', '=', 'apply_material.project_id')
            ->orderBy('apply_material.is_emergency', 'desc')
            ->paginate($n, [
                'am_id as amId',
                'number',
                'url',
                'category_material.name as categoryName',
                'specification',
                'amount',
                'apply_material.unit',
                'category_project.name as projectName',
                'user_id as userId',
                'apply_material.create_at as createAt',
                'type',
                'purpose',
                'state',
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function cancelMaterialApply($id, $data = [])
    {
        self::isApplyExist($id, $state = 0);

        self::where('am_id', $id)->update([
            'is_del' => 1,
            'reason' => $data['reason'],
            'update_at' => time(),
        ]);

        return true;
    }

    public static function auditMaterialApply($id, $type, $data = [])
    {
        if ($type == 1){
            self::isApplyExist($id, $state = 10);
            $state = $data['is_through'] == 1 ? 12 : 11;
        }else {
            self::isApplyExist($id, $state = 0);
            $state = $data['is_through'] == 1 ? 2 : 1;
        }

        $data['create_at'] = time();
        self::changeState($id, $state);
        return DB::table('audit_material')->insertGetId($data);
    }

    public static function selectMaterialApply($id)
    {
        $data = self::where([
            ['am_id', $id],
            ['is_del', 0]
        ])->select('*')->first();

        if (! $data) {
            throw new Exception('该元素不存在@404', 2003);
        }

        return  $data;
    }

    public static function changeState($id, $state)
    {
        $result = self::where([
            ['am_id', $id],
            ['is_del', '0'],
        ])->select('am_id')->first();
        if (!$result) {
            throw new Exception('该申请不存在@404', 3007);
        }

        self::where('am_id', $id)->update([
            'state' => $state,
            'update_at' => time(),
        ]);

        return true;

    }
    
    public static function searchMaterialApply($p, $n, $type, $state)
    {
        $searchCondition = [['apply_material.is_del', '0']];
        
        if ($type !== '') {
            $searchCondition[] = ['apply_material.type', $type];
        }
        if ($state !== '') {
            $searchCondition[] = ['apply_material.state', $state];
        }
        
        $data = self::where($searchCondition)
            ->join('category_material', 'category_material.id', '=', 'apply_material.category_id')
            ->join('category_project', 'category_project.id', '=', 'apply_material.project_id')
            ->orderBy('apply_material.is_emergency', 'desc')
            ->paginate($n, [
                'am_id as amId',
                'number',
                'url',
                'category_material.name as categoryName',
                'specification',
                'amount',
                'apply_material.unit',
                'category_project.name as projectName',
                'user_id as userId',
                'apply_material.create_at as createAt',
                'type',
                'purpose',
                'state',
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function isApplyExist($id, $state = 0)
    {
        $result = self::where([
            ['am_id', $id],
            ['state', $state],
            ['is_del', '0']
        ])->select('am_id')->first();
        if (!$result) {
            throw new Exception('该申请不存在,或已被操作@404', 3007);
        }
    }
    
    
}