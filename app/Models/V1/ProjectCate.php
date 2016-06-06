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
class ProjectCate extends BaseModel
{
    protected $table = 'category_project';
    public $timestamps = false;

    public static function createCategory($data=[])
    {
        $name = $data['name'] ? $data['name'] : '';
        $userId = $data['user_name'] ? $data['user_name'] : '';

        $result = self::where('is_del', 0)
            ->where(function ($query) use ($name, $userId) {
                $query->where('name', $name);
            })
            ->select('id')
            ->first();

        if ($result) {
            throw new Exception('该项目已经存在@409', 3001);
        }

        $data['create_at'] = time();

        return self::insertGetId($data);
    }

    public static function selectCategoryById($id, $fields=['id', 'name'])
    {
        $data = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该项目不存在@404', 3011);
        }

        return $data->toArray();
    }


    public static function listCategory($p, $n, $fields=['id', 'name', 'user_name','create_at'])
    {
        $data = self::where('is_del', 0)->paginate($n, $fields, 'p', $p);

        return self::getPageData($data);
    }

    public static function updateCategory($id, $data=[])
    {

        $name = $data['name'] ? $data['name'] : '';
        $userId = $data['user_name'] ? $data['user_name'] : '';

        $result = self::where([
                ['is_del', 0],
                ['id', '<>', $id]
            ])->where(function ($query) use ($name, $userId) {
                $query->where('name', $name);
            })
            ->select('id')
            ->first();

        if ($result) {
            throw new Exception('该项目名称已经存在@409', 3003);
        }

        $result = self::where([
                ['id', $id],
                ['is_del', 0]
            ])->select('id')->first();
        if (! $result) {
            throw new Exception('该项目不存在@404', 3005);
        }

        $data['updated_at'] = time();
        self::where('id', $id)->update($data);

        return true;
    }

    public static function deleteCategory($id)
    {
        $result = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select('id')->first();
        if (! $result) {
            throw new Exception('该项目不存在@404', 3007);
        }

        $result = DB::table('element')
            ->where([
                ['category_id', $id],
                ['is_del', 0]
            ])
            ->count();
        if ($result) {
            throw new Exception('该类别已经被'. $result . '个材料引用,无法删除@403', 3009);
        }

        self::where('id', $id)->update([
            'is_del' => 1,
            'updated_at' => time(),
        ]);

        return true;
    }

    public static function countCategory()
    {
        return self::where('is_del', 0)->count();
    }
}