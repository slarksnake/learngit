<?php
namespace App\Http\Controllers\Admin\V1;

use Exception;
use App\Models\V1\Material;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MaterialController extends Controller
{
    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material 元素 - 列表
     * @apiName material_list
     * @apiGroup Web_Material
     *
     * @apiParam {Integer} [p=1]  可选,元素信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量,上限100
     * @apiParam {String}  [col] 排序列名 sn:编号, en_name:英文名称, cn_name:中文名称, price:价格
     * @apiParam {Integer} [order=1] 1:升序 0:降序
     *
     * @apiParam {Integer} [cate]  搜索 元素分类ID
     * @apiParam {String}  [name]  搜索 元素中/英文名称
     * @apiParam {String}  [sn]    搜索 元素编号
     *
     * @apiSuccess {Integer} msg  元素总数
     * @apiSuccess {Object}  data 元素列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": 100,
     *     "data": [
     *       {
     *           "id": 1,
     *           "email": "xxx@qq.com",
     *           "created_at": 1458492034
     *       },
     *       {
     *           "id": 2,
     *           "email": "ooo@163.com",
     *           "created_at": 1458553103
     *       },
     *     ],
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material
     */
    public function index(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1|max:100',
            'name' => 'bail|filled|string|max:10',
            'cate' => 'bail|filled|integer|min:1',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $name = $request->input('name', '');
        $cate = $request->input('cate', '');

        try {
            list($total, $data) = Material::listMaterial($page, $num, $name, $cate);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => $total, 'data' => $data, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/:id 元素 - 查看
     * @apiName material_select
     * @apiGroup Web
     *
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": {
     *       "id": 1,
     *       "account": "a@xx.com",
     *       "name": "xxx",
     *       "character_id": 1,
     *       "is_closed": 0
     *     },
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material
     */
    public function select($id)
    {
        try {
            $data = material::selectmaterialById($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $data, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/material 元素 - 创建
     * @apiName material_create
     * @apiGroup Web
     *
     * @apiParam {Integer} category 元素分类ID
     * @apiParam {String}  sn       元素编号
     * @apiParam {String}  en_name  元素英文名称
     * @apiParam {String}  cn_name  元素中文名称
     * @apiParam {Float}   percent  元素浓度
     * @apiParam {String}  factory  供应商
     * @apiParam {Float}   price    价格
     *
     * @apiSuccess {Integer}  data 元素ID
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material
     */
    public function create(Request $request)
    {
        $data = $this->check($request);

        try {
            $id = material::createMaterial($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {put} web/v1/material 元素 - 编辑
     * @apiName material_update
     * @apiGroup Web
     *
     * @apiParam {Integer} category 元素分类ID
     * @apiParam {String}  sn       元素编号
     * @apiParam {String}  en_name  元素英文名称
     * @apiParam {String}  cn_name  元素中文名称
     * @apiParam {Float}   percent  元素浓度
     * @apiParam {String}  factory  供应商
     * @apiParam {Float}   price    价格
     *
     * @apiSuccess {Integer}  data 元素ID
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material
     */
    public function update(Request $request, $id)
    {
        $data = $this->check($request);

        try {
            material::updatematerial($id, $data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {delete} web/v1/material/:id 元素 - 删除
     * @apiName material_delete
     * @apiGroup Web
     *
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1,
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material/:id
     */
    public function delete($id)
    {
        try {
            material::deletematerial($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    private function check(Request $request)
    {
        $message = [
            'category' => '元素类别必须填写',
            'sn.required' => '元素编号必须填写',
            'sn.max' => '元素编号最多10个字符',
            'sn.eng_dash' => '元素编号不合法',
            'en_name.required' => '英文名称必须填写',
            'en_name.max' => '英文名称最多30个字符',
            'en_name.eng_dash' => '元素英文名不合法',
            'cn_name.required' => '中文名称必须填写',
            'cn_name.max' => '中文名称最多30个字符',
            'cn_name.regex' => '元素中文名称不合法',
            'percent.required' => '元素浓度百分比必须填写',
            'percent.numeric' => '元素浓度必须是数字',
            'percent.max' => '元素浓度为0~100,最多保留3位小数',
            'percent.regex' => '元素浓度为0~100,最多保留3位小数',
            'factory.required' => '供应商必须填写',
            'factory.max' => '供应商名称最多20个字符',
            'price.required' => '价格必须填写',
            'price.numeric' => '价格必须是数字',
            'price.between' => '价格区间为0~9999999.99',
        ];
        $this->validate($request, [
            'category' => 'bail|required|integer|min:1',
            'sn' => 'bail|required|eng_dash|max:10',
            'en_name' => 'bail|required|eng_dash|max:30',
            'cn_name' => 'bail|required|regex:/\p{Han}/u|max:30',
            'percent' => 'bail|required|numeric|max:100|regex:/^\d{1,3}(\.\d{1,3})?$/',
            'factory' => 'bail|required|max:20',
            'price' => 'bail|required|numeric|between:0,9999999.99',
        ], $message);

        $data['category_id'] = $request->input('category');
        $data['sn'] = $request->input('sn');
        $data['en_name'] = $request->input('en_name');
        $data['cn_name'] = $request->input('cn_name');
        $data['percent'] = $request->input('percent');
        $data['factory'] = $request->input('factory');
        $data['price'] = $request->input('price');

        $data['cn_name'] = preg_replace('/\'/', '', $data['cn_name']);

        return $data;
    }


    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/auto?type=:type&value=:value 元素 - 自动补全
     * @apiName material_auto
     * @apiGroup Web
     *
     * @apiParam {String} type  元素搜索类型 sn:编号 en:英文名称 cn:中文名称
     * @apiParam {String} value 搜索关键字
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": {
     *       "id": 1,
     *       "account": "a@xx.com",
     *       "name": "xxx",
     *       "character_id": 1,
     *       "is_closed": 0
     *     },
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://api.smell.renrenfenqi.com/web/v1/material/auto
     */
    public function auto(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'type' => 'bail|required|in:sn,en,cn',
            'value' => 'bail|required|string|max:100'
        ], $message);

        $type = $request->input('type');
        $value = $request->input('value');

        try {
            $data = material::autoCompletematerial($type, $value);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $data, 'code' => 1]);
    }


    public function search(Request $request)
    {
        $message = [];
        $this->validate($request, [
            'sn' => 'bail|filled|string|max:10',
            'name' => 'bail|filled|string|max:10',
            'cate' => 'bail|filled|integer|min:1',
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1',
        ], $message);

        $sn = $request->input('sn', '');
        $name = $request->input('name', '');
        $cate = $request->input('cate', '');
        $p = $request->input('p', 1);
        $n = $request->input('n', 10);

        try {
            list($total, $data) = material::searchmaterial($sn, $name, $cate, $p, $n);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => $total, 'data' => $data, 'code' => 1]);
    }
}