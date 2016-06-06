<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class FormulaCate
 * @package App\Models\V1
 *
 * 错误码 4000~4999
 */
class FormulaCate extends BaseModel
{
    protected $table = 'category_formula';
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
            throw new Exception('该分类已经存在@409', 4001);
        }

        $data += ['created_at' => time()];
        return self::insertGetId($data);
    }

    public static function selectCategoryById($id, $fields=['id', 'en_name', 'cn_name'])
    {
        $data = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select($fields)->first();

        if (! $data) {
            throw new Exception('该元素分类不存在@404', 4003);
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
            ])->where(function ($query) use ($cn_name, $en_name) {
                $query->where('cn_name', $cn_name)
                    ->orWhere('en_name', $en_name);
            })
            ->select('id')
            ->first();

        if ($result) {
            throw new Exception('该分类名称(中/英)已经存在@409', 4005);
        }

        $result = self::where([
            ['id', $id],
            ['is_del', 0]
        ])->select('id')->first();
        if (! $result) {
            throw new Exception('该分类不存在@404', 4007);
        }

        $data += ['updated_at' => time()];
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
            throw new Exception('该分类不存在@404', 4009);
        }

        $result = DB::table('formula')
            ->where([
                ['category_id', $id],
                ['is_del', 0]
            ])
            ->count();
        if ($result) {
            throw new Exception('该类别已经被'. $result . '个配方引用,无法删除@403', 4011);
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