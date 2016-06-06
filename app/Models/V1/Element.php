<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class Element
 * @package App\Models\V1
 *
 * 错误码 2000~2999
 */
class Element extends BaseModel
{
    protected $table = 'element';
    public $timestamps = false;

    public static function createElement($data=[])
    {
        self::isElementNameUsed($data);

        $result = ElementCate::selectCategoryById($data['category_id'], 'id');
        if (! $result) {
            throw new Exception('元素类别不存在@404', 2005);
        }

        $data['created_at'] = time();

        return self::insertGetId($data);
    }

    public static function updateElement($id, $data=[])
    {
        self::isElementNameUsed($data, $id);
        self::isFormulaUsed($id);

        $result = ElementCate::selectCategoryById($data['category_id'], 'id');
        if (! $result) {
            throw new Exception('元素类别不存在@404', 2009);
        }

        $data['updated_at'] = time();

        self::where('ele_id', $id)->update($data);
        return true;
    }


    public static function deleteElement($id)
    {
        self::isFormulaUsed($id);

        $data = self::where([
            ['ele_id', $id],
            ['is_del', 0]
        ])->select('ele_id')->first();

        if (! $data) {
            throw new Exception('该元素不存在@404', 2011);
        }

        self::where('ele_id', $id)->update([
            'is_del' => 1,
            'updated_at' => time()
        ]);

        return true;
    }

    public static function listElement($p, $n, $col, $order, $sn, $name, $cate)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

//        $data = self::where('element.is_del', 0)
//            ->join('category_element', 'category_element.id', '=', 'category_id')
//            ->orderBy('element.'.$col, $order)
//            ->paginate($n, [
//                'ele_id',
//                'sn',
//                'element.en_name',
//                'element.cn_name',
//                'percent',
//                'factory',
//                'price',
//                'category_element.en_name as en_cate',
//                'category_element.cn_name as cn_cate'
//            ], 'p', $p);
//
//        return self::getPageData($data);

        $searchCondition = [['element.is_del', 0]];
        if ($sn !== '') {
            $searchCondition[] = ['element.sn', 'like', $sn.'%'];
        }
        if ($cate !== '') {
            $searchCondition[] = ['element.category_id', $cate];
        }

        if ($name !== '') {
            $data = self::where($searchCondition)
                ->where(function ($query) use ($name) {
                    $query->where('element.cn_name', 'like', $name.'%')
                        ->orWhere('element.en_name', 'like', $name.'%');
                })
                ->join('category_element', 'category_element.id', '=', 'element.category_id')
                ->orderBy('element.'.$col, $order)
                ->paginate($n, [
                    'ele_id',
                    'sn',
                    'element.en_name',
                    'element.cn_name',
                    'percent',
                    'factory',
                    'price',
                    'category_element.en_name as en_cate',
                    'category_element.cn_name as cn_cate'
                ], 'p', $p);

        } else {
            $data = self::where($searchCondition)
                ->join('category_element', 'category_element.id', '=', 'element.category_id')
                ->orderBy('element.'.$col, $order)
                ->paginate($n, [
                    'ele_id',
                    'sn',
                    'element.en_name',
                    'element.cn_name',
                    'percent',
                    'factory',
                    'price',
                    'category_element.en_name as en_cate',
                    'category_element.cn_name as cn_cate'
                ], 'p', $p);
        }

        return self::getPageData($data);
    }

    public static function selectElementById($id)
    {
        $data = self::where([
            ['ele_id', $id],
            ['is_del', 0]
        ])->select('*')->first();

        if (! $data) {
            throw new Exception('该元素不存在@404', 2003);
        }

        $data = $data->toArray();
        $categoryId = $data['category_id'];

        $categoryData = ElementCate::selectCategoryById($categoryId);
        if ($categoryData['en_name'] && $categoryData['cn_name']) {
            $categoryName = $categoryData['cn_name'] . '(' . $categoryData['en_name'] . ')';
        } else {
            $categoryName = implode('', $categoryData);
        }

        $data['category_name'] = $categoryName;
        $data['is_used'] = 0;   // 没有被配方引用

        try {
            self::isFormulaUsed($id);
        } catch (Exception $e) {
            $data['is_used'] = 1;
        }

        return $data;
    }

    public static function selectElementBySn($sn)
    {
        $data = self::where([
                ['element.is_del', 0],
                ['element.sn', 'like', $sn.'%']
            ])
            ->join('category_element', 'category_element.id', '=', 'element.category_id')
            ->select(
                'element.ele_id',
                'element.sn',
                'element.en_name',
                'element.cn_name',
                'element.percent',
                'element.factory',
                'element.price',
                'category_element.en_name as cate_en_name',
                'category_element.cn_name as cate_cn_name'
            )
            ->take(10)
            ->get();

        return $data ? $data->toArray() : [];
    }

    public static function selectElementByCNName($name)
    {
        $data = self::where([
                ['element.is_del', 0],
                ['element.cn_name', 'like', $name.'%']
            ])
            ->join('category_element', 'category_element.id', '=', 'element.category_id')
            ->select(
                'element.ele_id',
                'element.sn',
                'element.en_name',
                'element.cn_name',
                'element.percent',
                'element.factory',
                'element.price',
                'category_element.en_name as cate_en_name',
                'category_element.cn_name as cate_cn_name'
            )
            ->take(10)
            ->get();

        return $data ? $data->toArray() : [];
    }

    public static function selectElementByENName($name)
    {
        $data = self::where([
                ['element.is_del', 0],
                ['element.en_name', 'like', $name.'%']
            ])
            ->join('category_element', 'category_element.id', '=', 'element.category_id')
            ->select(
                'element.ele_id',
                'element.sn',
                'element.en_name',
                'element.cn_name',
                'element.percent',
                'element.factory',
                'element.price',
                'category_element.en_name as cate_en_name',
                'category_element.cn_name as cate_cn_name'
            )
            ->take(10)
            ->get();

        return $data ? $data->toArray() : [];
    }

    public static function countElement()
    {
        return self::where('is_del', 0)->count();
    }

    public static function autoCompleteElement($type, $value)
    {
        $searchFunction = 'selectElementBySn';

        switch ($type) {
            case 'sn':
                $searchFunction = 'selectElementBySn';
                break;
            case 'en':
                $searchFunction = 'selectElementByENName';
                break;
            case 'cn':
                $searchFunction = 'selectElementByCNName';
                break;
        }

        return self::$searchFunction($value);
    }

    public static function searchElement($sn, $name, $cate, $p, $n)
    {
        $searchCondition = [['element.is_del', 0]];
        if ($sn !== '') {
            $searchCondition[] = ['element.sn', 'like', $sn.'%'];
        }
        if ($cate !== '') {
            $searchCondition[] = ['element.category_id', $cate];
        }

        if ($name !== '') {
            $data = self::where($searchCondition)
                ->where(function ($query) use ($name) {
                    $query->where('element.cn_name', 'like', $name.'%')
                        ->orWhere('element.en_name', 'like', $name.'%');
                })
                ->join('category_element', 'category_element.id', '=', 'element.category_id')
                ->paginate($n, [
                    'ele_id',
                    'sn',
                    'element.en_name',
                    'element.cn_name',
                    'percent',
                    'factory',
                    'price',
                    'category_element.en_name as en_cate',
                    'category_element.cn_name as cn_cate'
                ], 'p', $p);

        } else {
            $data = self::where($searchCondition)
                ->join('category_element', 'category_element.id', '=', 'element.category_id')
                ->paginate($n, [
                    'ele_id',
                    'sn',
                    'element.en_name',
                    'element.cn_name',
                    'percent',
                    'factory',
                    'price',
                    'category_element.en_name as en_cate',
                    'category_element.cn_name as cn_cate'
                ], 'p', $p);
        }

        return self::getPageData($data);
    }

    private static function isFormulaUsed($eleId)
    {
        $result = DB::table('formula_element')->where([
            ['ele_id', $eleId],
            ['is_del', 0]
        ])->select('formula_id')->first();
        if ($result) {
            throw new Exception('该元素已被配方引用,禁止修改', 2013);
        }
    }

    private static function isElementNameUsed($data, $id=null)
    {
        $sn = $data['sn'];
        $en_name = $data['en_name'];
        $cn_name = $data['cn_name'];
        $percent = $data['percent'];
        // 元素编号SN或者名称+浓度唯一
        $where = "is_del=0 AND (sn='$sn' OR (en_name='$en_name' OR cn_name='$cn_name') AND percent='$percent')";
        if ($id) {
            $where .= " AND ele_id<>$id";
        }
        $result = self::whereRaw($where)->select('ele_id')->first();
        if ($result) {
            throw new Exception('元素编号或名称+浓度必须唯一@409', 2001);
        }
    }
}