<?php

namespace App\Models\V1;

use App\Models\BaseModel;
use DB;
use Exception;

/**
 * Class Formula
 * @package App\Models\V1
 *
 * 错误码 5000~5999
 */
class Formula extends BaseModel
{
    protected $table = 'formula';
    public $timestamps = false;

    public static function checkCategory($id)
    {
        $result = DB::table('category_formula')->where([
            ['is_del', 0],
            ['id', $id]
        ])->select('id')->first();
        if (! $result) {
            throw new Exception('配方类别不存在@404', 5005);
        }

        return true;
    }

    public static function checkFormula($id, $fields=['id'])
    {
        $result = self::where([
                ['is_del', 0],
                ['id', $id]
            ])->select($fields)->first();
        if (! $result) {
            throw new Exception('配方不存在@404', 5009);
        }

        return $result ? $result->toArray() : [];
    }

    public static function createFormula($data=[])
    {
        self::checkCategory($data['category_id']);
        self::isFormulaNameUsed($data);
        $time = time();

        DB::beginTransaction();
        try {
            $elementList = $data['elements'];

            $data['elements'] = count($elementList);
            $data['created_at'] = $time;
            $formulaId = self::insertGetId($data);

            foreach ($elementList as $k => $v) {
                $elementList[$k]['formula_id'] = $formulaId;
                // 非溶剂
                if (! isset($elementList[$k]['is_main'])) {
                    $elementList[$k]['is_main'] = 0;
                }
                $elementList[$k]['created_at'] = $time;
            }

            DB::table('formula_element')->insert($elementList);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('数据库错误,配方添加失败', 5003);
        }

        return $formulaId;
    }

    public static function selectFormulaById($id)
    {
        $formulaData = self::where([
                ['formula.is_del', 0],
                ['formula.id', $id]
            ])
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->select(
                'category_formula.id as cate_id',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name'
            )
            ->first();

        if (! $formulaData) {
            throw new Exception('配方不存在@404', 5007);
        }

        $formulaData = $formulaData->toArray();

        $formulaElement = DB::table('formula_element')
                ->where([
                    ['formula_id', $id],
                    ['formula_element.is_del', 0]
                ])
                ->join('element', 'element.ele_id', '=', 'formula_element.ele_id')
                ->join('category_element', 'element.category_id', '=', 'category_element.id')
                ->select(
                    'element.ele_id as ele_id',
                    'sn',
                    'element.en_name as element_en_name',
                    'element.cn_name as element_cn_name',
                    'element.percent as element_percent',
                    'formula_element.percent as formula_percent',
                    'formula_element.is_main',
                    'factory',
                    'price',
                    'category_element.en_name as cate_ele_en_name',
                    'category_element.cn_name as cate_ele_cn_name'
                )
                ->get();

        $formulaData['main_element'] = '';

        foreach ($formulaElement as $k => $v) {
            if ($v->is_main) {
                $formulaData['main_element'] = $formulaElement[$k];
                unset($formulaElement[$k]);
                break;
            }
        }

        $formulaData['elements'] = $formulaElement;

        return $formulaData;
    }

    // 更新模式为配方元素全量软删除和新增 相当于保留每次配方元素的历史记录
    public static function updateFormula($id, $data=[])
    {
        self::checkFormula($id, 'created_at');
        self::checkCategory($data['category_id']);
        self::isFormulaNameUsed($data, $id);

        $time = time();

        DB::beginTransaction();
        try {
            $elementList = $data['elements'];

            $data['elements'] = count($elementList);
            $data['updated_at'] = $time;

            self::where('id', $id)->update($data);

            foreach ($elementList as $k => $v) {
                $elementList[$k]['formula_id'] = $id;
                // 非溶剂
                if (! isset($elementList[$k]['is_main'])) {
                    $elementList[$k]['is_main'] = 0;
                }
                $elementList[$k]['created_at'] = $time;
            }

            DB::table('formula_element')
                ->where([
                    ['formula_id', $id],
                    ['updated_at', 0]
                ])
                ->update([
                    'is_del' => 1,
                    'updated_at' => $time,
                ]);
            DB::table('formula_element')->insert($elementList);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('数据库错误,配方更新失败', 5003);
        }

        return true;
    }

    public static function deleteFormula($id)
    {
        $time = time();

        DB::beginTransaction();
        try {
            self::where('id', $id)->update([
                'is_del' => 1,
                'updated_at' => $time,
            ]);
            DB::table('formula_element')
                ->where([
                    ['formula_id', $id],
                    ['updated_at', 0]
                ])
                ->update([
                    'is_del' => 1,
                    'updated_at' => $time,
                ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception('数据库错误,配方删除失败', 5011);
        }

        return true;
    }

    public static function listForumula($p, $n, $col, $order)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($col == 'time') {
            $col = 'created_at';
        }

        $data = self::where('formula.is_del', 0)
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->orderBy('formula.'.$col, $order)
            ->paginate($n, [
                'formula.id as id',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'formula.created_at as created_at',
                'formula.updated_at as updated_at',
                'update_name',
                'create_name'
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function countFormula()
    {
        return self::where('is_del', 0)->count();
    }


    private static function isFormulaNameUsed($data, $id=null)
    {
        $sn = $data['sn'];
        $en_name = $data['en_name'];
        $cn_name = $data['cn_name'];
        // 配方编号SN或者名称唯一
        $where = "is_del=0 AND (sn='$sn' OR en_name='$en_name' OR cn_name='$cn_name' )";
        if ($id) {
            $where .= " AND id<>$id";
        }
        $result = self::whereRaw($where)->select('id')->first();
        if ($result) {
            throw new Exception('该配方编号或名称已经存在@409', 5001);
        }
    }


    public static function searchFormulaByCategory($keyword, $p, $n, $col, $order)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($col == 'time') {
            $col = 'created_at';
        }


        $cateData = DB::table('category_formula')
            ->where('is_del', 0)
            ->where(function ($query) use ($keyword) {
                $query->where('cn_name', $keyword)
                    ->orWhere('en_name', $keyword);
            })
            ->select('id', 'en_name', 'cn_name')
            ->first();
        if (! $cateData) {
            throw new Exception('没有这个配方分类@404', 5013);
        }

        $cateData = $cateData->id;

        $data = self::where([
                ['formula.is_del', 0],
                ['formula.category_id', $cateData]
            ])
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->orderBy('formula.'.$col, $order)
            ->paginate($n, [
                'formula.id as id',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'formula.created_at as created_at',
                'formula.updated_at as updated_at',
                'update_name',
                'create_name'
            ], 'p', $p);

        list ($total, $result) = self::getPageData($data);
        if (! $total) {
            throw new Exception('没有找到这个类别的配方@404', 5015);
        }

        return [$total, $result];
    }

    public static function searchFormulaBySN($keyword, $p, $n, $col, $order)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($col == 'time') {
            $col = 'created_at';
        }

        $data = self::where([
                ['formula.is_del', 0],
                ['formula.sn', 'like', $keyword.'%']
            ])
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->orderBy('formula.'.$col, $order)
            ->paginate($n, [
                'formula.id as id',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'formula.created_at as created_at',
                'formula.updated_at as updated_at',
                'update_name',
                'create_name'
            ], 'p', $p);

        list($total, $result) = self::getPageData($data);
        if (! $total) {
            throw new Exception('没有找到这个编号的配方@404', 5017);
        }

        return [$total, $result];
    }

    public static function searchFormulaByElement($keyword, $p, $n, $col, $order)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($col == 'time') {
            $col = 'created_at';
        }

        $data = DB::table('element')
                ->where('is_del', 0)
                ->where(function ($query) use ($keyword) {
                    $query->where('cn_name', 'like', $keyword.'%')
                        ->orWhere('en_name', 'like', $keyword.'%');
                })
                ->select('ele_id')
                ->get();

        if (! $data) {
            throw new Exception('没有找到该元素@404', 5019);
        }

        $dataArray = [];
        foreach ($data as $v) {
            $dataArray[] = $v->ele_id;
        }

        $data = DB::table('formula_element')
            ->where('is_del', 0)
            ->whereIn('ele_id', $dataArray)
            ->select('formula_id')
            ->distinct()
            ->get();

        if (! $data) {
            throw new Exception('没有包含该元素的配方@404', 5021);
        }

        $dataArray = [];
        foreach ($data as $v) {
            $dataArray[] = $v->formula_id;
        }

        $data = self::where('formula.is_del', 0)
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->whereIn('formula.id', $dataArray)
            ->orderBy('formula.'.$col, $order)
            ->paginate($n, [
                'formula.id as id',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'formula.created_at as created_at',
                'formula.updated_at as updated_at',
                'update_name',
                'create_name'
            ], 'p', $p);

        return self::getPageData($data);
    }

    public static function searchFormulaByFounder($keyword, $p, $n, $col, $order)
    {
        if ($order) {
            $order = 'desc';
        } else {
            $order = 'asc';
        }

        if ($col == 'time') {
            $col = 'created_at';
        }

        $data = self::where([
                ['formula.is_del', 0],
                ['formula.create_name', 'like', $keyword.'%']
            ])
            ->join('category_formula', 'category_formula.id', '=', 'formula.category_id')
            ->orderBy('formula.'.$col, $order)
            ->paginate($n, [
                'formula.id as id',
                'sn',
                'formula.en_name as formula_en_name',
                'formula.cn_name as formula_cn_name',
                'category_formula.en_name as cate_en_name',
                'category_formula.cn_name as cate_cn_name',
                'formula.created_at as created_at',
                'formula.updated_at as updated_at',
                'update_name',
                'create_name'
            ], 'p', $p);

        return self::getPageData($data);
    }

}