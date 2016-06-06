<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class ElementCate
 * @package App\Models\V1
 *
 * 错误码 3000~3999
 */
class ElementCate extends BaseModel
{
    protected $table = 'category_element';
    public $timestamps = false;

    public static function createCategory($data=[])
    {
        $en_name = $data['en_name'] ? $data['en_name'] : 0;
        $cn_name = $data['cn_name'] ? $data['cn_name'] : 0;

        $result = self::where('is_del', 0)
            ->where(function ($query) use ($en_name, $cn_name) {
                $query->where('cn_name', $cn_name)
                      ->orWhere('en_name', $en_name);
            })
            ->select('id')
            ->first();

        if ($result) {
            throw new Exception('该分类已经存在@409', 3001);
        }

        $data['created_at'] = time();

        return self::insertGetId($data);
    }

    public static function selectCategoryById($id, $fields=['id', 'en_name', 'cn_name'])
    {
        $data = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该元素分类不存在@404', 3011);
        }

        return $data->toArray();
    }


    public static function listCategory($p, $n, $fields=['id', 'en_name', 'cn_name'])
    {
        $data = self::where('is_del', 0)->paginate($n, $fields, 'p', $p);

        return self::getPageData($data);
    }

    public static function updateCategory($id, $data=[])
    {
        $en_name = $data['en_name'] ? $data['en_name'] : 0;
        $cn_name = $data['cn_name'] ? $data['cn_name'] : 0;

        $result = self::where([
                ['is_del', 0],
                ['id', '<>', $id]
            ])->where(function ($query) use ($en_name, $cn_name) {
                $query->where('cn_name', $cn_name)
                    ->orWhere('en_name', $en_name);
            })
            ->select('id')
            ->first();

        if ($result) {
            throw new Exception('该分类名称(中/英)已经存在@409', 3003);
        }

        $result = self::where([
                ['id', $id],
                ['is_del', 0]
            ])->select('id')->first();
        if (! $result) {
            throw new Exception('该分类不存在@404', 3005);
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
            throw new Exception('该分类不存在@404', 3007);
        }

        $result = DB::table('element')
            ->where([
                ['category_id', $id],
                ['is_del', 0]
            ])
            ->count();
        if ($result) {
            throw new Exception('该类别已经被'. $result . '个元素引用,无法删除@403', 3009);
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