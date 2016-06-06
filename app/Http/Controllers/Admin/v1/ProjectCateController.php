<?php

namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\V1\ProjectCate;


class ProjectCateController extends Controller
{
    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/project/category 项目管理 - 列表
     * @apiName project_category_list
     * @apiGroup Web_Project_Category
     *
     * @apiParam {Integer} [p=1]  可选,项目管理信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量
     *
     * @apiSuccess {Integer} msg  项目管理总数
     * @apiSuccess {Object}  data 项目管理列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": 100,
     *     "data": [
     *       {
     *           "id": 1,
     *           "name": "test"
     *           "username": "mis"
     *           "create_at": "1464860989"
     *       },
     *       {
     *           "id": 1,
     *           "name": "0000"
     *           "username": "韩懿莹"
     *           "create_at": "1464860989"
     *       },
     *     ],
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/project/category
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
            list($total, $data) = projectCate::listCategory($page, $num);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>$total, 'data'=>$data, 'code'=>1]);
    }


    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/project/category 项目管理 - 创建
     * @apiName project_category_create
     * @apiGroup Web_Project_Category
     *
     * @apiParam {String} userName 项目管理立项人
     * @apiParam {String} name 项目管理中文名称
     *
     * @apiSuccess {Integer}  data 项目管理ID
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
     * @apiSampleRequest http://localhost/blog/web/v1/project/category/creatre
     */
    public function create(Request $request)
    {
        $message = [
            'name.required' => '名称必须必须填写',
            'userName.required' => '立项人必须填写',
            'name.max' => '中文名称最多20个字符',
            'userName.max' => '立项人最多20个字符',
            'name.not_in' => '中文名称不合法',
            'userName.not_in' => '立项人名称不合法',
        ];
        $this->validate($request, [
            'name' => 'bail|required:name|max:20|not_in:0,undefined,null',
            'userName' => 'bail|required:userName|max:20|not_in:0,undefined,null'
        ], $message);

        $data['name'] = $request->input('name', '');
        $data['user_name'] = $request->input('userName', '');

        try {
            $id = projectCate::createCategory($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>$id, 'code'=>1]);
    }


    /**
     * @apiVersion 1.0.0
     *
     * @api {put} web/v1/project/category/:id 项目管理 - 编辑
     * @apiName project_category_update
     * @apiGroup Web_Project_Category
     *
     * @apiParam {String} userName 项目管理立项人
     * @apiParam {String} name 项目管理中文名称
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
     * @apiSampleRequest http://localhost/blog/web/v1/project/category/:id
     */
    public function update(Request $request, $id)
    {
        $message = [
            'name.required_without' => '名称必须必须填写',
            'userName.required_without' => '立项人必须填写',
            'name.max' => '中文名称最多20个字符',
            'userName.max' => '立项人最多20个字符',
            'name.not_in' => '中文名称不合法',
            'userName.not_in' => '立项人名称不合法',
        ];
        $this->validate($request, [
            'name' => 'bail|required_without:name|max:20|not_in:0,undefined,null',
            'userName' => 'bail|required_without:userName|max:20|not_in:0,undefined,null'
        ], $message);

        $data['name'] = $request->input('name', '');
        $data['user_name'] = $request->input('userName', '');

        try {
            projectCate::updateCategory($id, $data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>'', 'code'=>1]);
    }



    /**
     * @apiVersion 1.0.0
     *
     * @api {delete} web/v1/project/category/:id 项目管理 - 删除
     * @apiName project_category_delete
     * @apiGroup Web_Project_Category
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
     * @apiSampleRequest http://localhost/blog/web/v1/project/category/:id
     */
    public function delete($id)
    {
        try {
            projectCate::deleteCategory($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>'', 'code'=>1]);
    }
}