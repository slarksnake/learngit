<?php

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\V1\MaterialCate;


class MaterialCateController extends Controller
{
    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/category 材料分类 - 列表
     * @apiName material_category_list
     * @apiGroup Web_Material_Cate
     *
     * @apiParam {Integer} [p=1]  可选,材料分类信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量
     *
     * @apiSuccess {Integer} msg  材料分类总数
     * @apiSuccess {Object}  data 材料分类列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": 100,
     *     "data": [
     *       {
     *           "id": 1,
     *           "name": "test"
     *       },
     *       {
     *           "id": 2,
     *           "name": "ooo",
     *       },
     *     ],
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/category
     */
    public function index(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1'
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);

        try {
            list($total, $data) = materialCate::listCategory($page, $num);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>$total, 'data'=>$data, 'code'=>1]);
    }


    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/material/category 材料分类 - 创建
     * @apiName material_category_create
     * @apiGroup Web_Material_Cate
     *
     * @apiParam {String} name 材料分类中文名称
     *
     * @apiSuccess {Integer}  data 材料分类ID
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
     * @apiSampleRequest http://localhost/blog/web/v1/material/category/creatre
     */
    public function create(Request $request)
    {
        $message = [
            'name.required_with' => '名称必须至少填写一个',
            'name.max' => '中文名称最多20个字符',
            'name.not_in' => '中文名称不合法',
        ];
        $this->validate($request, [
            'name' => 'bail|required_with:en_name|max:20|not_in:0,undefined,null'
        ], $message);

        $data['name'] = $request->input('name', '');

        try {
            $id = materialCate::createCategory($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>$id, 'code'=>1]);
    }


    /**
     * @apiVersion 1.0.0
     *
     * @api {put} web/v1/material/category/:id 材料分类 - 编辑
     * @apiName material_category_update
     * @apiGroup Web_Material_Cate
     *
     * @apiParam {String} name 材料分类中文名称
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": ""
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/category/:id
     */
    public function update(Request $request, $id)
    {
        $message = [
            'name.required_with' => '名称必须填写',
            'name.max' => '中文名称最多20个字符',
            'name.not_in' => '中文名称不合法',
        ];
        $this->validate($request, [
            'name' => 'bail|required_with:en_name|max:20|not_in:0,undefined,null'
        ], $message);

        $data['name'] = $request->input('name', '');

        try {
            materialCate::updateCategory($id, $data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>'', 'code'=>1]);
    }



    /**
     * @apiVersion 1.0.0
     *
     * @api {delete} web/v1/material/category/:id 材料分类 - 删除
     * @apiName material_category_delete
     * @apiGroup Web_Material_Cate
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": ""
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/category/:id
     */
    public function delete($id)
    {
        try {
            materialCate::deleteCategory($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>'', 'code'=>1]);
    }
}