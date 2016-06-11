<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\V1\ApplyManage;


class ApplyManageController extends Controller
{

    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/apply 申请 - 列表
     * @apiName apply_list
     * @apiGroup Web_Apply_Management
     *
     * @apiParam {Integer} [p=1]  可选,元素信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量,上限100
     *
     * @apiParam {Integer} [userId]  搜索 用户ID
     * @apiParam {String}  [name]  搜索 材料名称
     *
     * @apiSuccess {Integer} msg  元素总数
     * @apiSuccess {Object}  data 元素列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
            "msg": 2,
            "data": [
                {
                    "am_id": 5,
                    "number": 10000,
                    "url": "http://www.baidu.com",
                    "categoryName": "办公品",
                    "specification": "BAM型",
                    "amount": 100,
                    "unit": "个",
                    "projectName": "大风",
                    "user_id": 33,
                    "create_at": 1465210116,
                    "type": 2,
                    "purpose": "电力系统以改善功率因数"
                    },
                    {
                    "am_id": 6,
                    "number": 20000,
                    "url": "http://www.baidu.com",
                    "categoryName": "办公品",
                    "specification": "BAM型",
                    "amount": 100,
                    "unit": "个",
                    "projectName": "大风",
                    "user_id": 33,
                    "create_at": 1465210121,
                    "type": 1,
                    "purpose": "电力系统以改善功率因数"
                    }
                ],
            "code": 1
        }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/apply
     */
    public function index(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1',
            'userId' => 'bail|filled|number',
            'name' => 'bail|filled|string|max:10',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $name = $request->input('name','');
        $userId = $request->input('userId','');

        try {
            list($total, $data) = ApplyManage::listMaterialApply($page, $num, $name, $userId);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => $total, 'data' => $data, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {put} web/v1/material/apply/:id 申请 - 取消
     * @apiName apply_cancel
     * @apiGroup Web_Apply_Management
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
     * @apiSampleRequest http://localhost/blog/web/v1/material/apply/:id
     */
    public function cancel(Request $request,$id)
    {
        $message = [
            'reason.required' => '原因必须填写',
            'reason.max' => '原因最多255个字符',
        ];
        $this->validate($request, [
            'reason' => 'bail|required|max:255',
        ], $message);

        $data['reason'] = $request->input('reason');
        try {
            ApplyManage::cancelMaterialApply($id,$data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => '1', 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/material/apply 申请 - 创建
     * @apiName apply_create
     * @apiGroup Web_Apply_Management
     *
     * @apiParam {Integer}   number    编号
     * @apiParam {String}   name    供货商
     * @apiParam {Integer}  category    材料类别编号
     * @apiParam {String}   url  供货商URL
     * @apiParam {String}   unit  单位
     * @apiParam {Integer}   purpose     数量
     * @apiParam {String}   purpose       用途
     * @apiParam {String}   project     材料项目类别
     * @apiParam {String}   specification     规格
     * @apiParam {Integer}   type    申请类别
     *
     * @apiSuccess {Integer}  data 供货商ID
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 9
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/apply
     */
    public function createProcurement(Request $request)
    {
        $data = $this->checkProcurement($request);

        try {
            $id = ApplyManage::createMaterialApply($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @api {put} web/v1/material/apply/procurement:id 采购申请 - 审核
     * @apiName apply_audit
     * @apiGroup Web_Apply_Management
     *
     *
     *@apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1,
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/apply/procurement:id
     */
    public function auditProcurement(Request $request,$id)
    {
        {
            $message = [
                'through.required' => '能否通过必须填写',
                'through.digits_between' => '能否通过必须是数字',
                'reason.required' => '原因必须填写',
                'reason.max' => '原因最多255个字符',
            ];
            $this->validate($request, [
                'through' => 'bail|required|digits_between:0,1',
                'reason' => 'bail|required|max:255',
            ], $message);

            $data['reason'] = $request->input('reason');
            $data['is_through'] = $request->input('through');
            try {
                ApplyManage::auditMaterialApply($id, $type = 1, $data);
            } catch (Exception $e) {
                return $this->logException($e);
            }

            return response()->json(['msg' => 'ok', 'data' => '1', 'code' => 1]);
        }
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/material/apply 申请 - 创建
     * @apiName apply_create
     * @apiGroup Web_Apply_Management
     *
     * @apiParam {Integer}   number    编号
     * @apiParam {String}   name    供货商
     * @apiParam {Integer}  category    材料类别编号
     * @apiParam {String}   url  供货商URL
     * @apiParam {String}   unit  单位
     * @apiParam {Integer}   amount     数量
     * @apiParam {String}   purpose       用途
     * @apiParam {String}   project     材料项目类别
     * @apiParam {String}   specification     规格
     * @apiParam {Integer}   type    申请类别
     *
     * @apiSuccess {Integer}  data 供货商ID
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
     * @apiSampleRequest http://localhost/blog/web/v1/material/apply
     */
    public function createRecipients(Request $request)
    {
        $data = $this->checkRecipients($request);

        try {
            $id = ApplyManage::createMaterialApply($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @api {put} web/v1/material/apply/audit:id 申请 - 取消
     * @apiName apply_audit
     * @apiGroup Web_Apply_Management
     *
     *
     *@apiSuccessExample Success-Response:
     * HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1,
     *     "code": 1
     *   }
     */
    public function auditRecipients(Request $request,$id)
    {
        {
            $message = [
                'through.required' => '能否通过必须填写',
                'through.number' => '能否通过必须是数字',
                'reason.required' => '原因必须填写',
                'reason.max' => '原因最多255个字符',
            ];
            $this->validate($request, [
                'through' => 'bail|required|number',
                'reason' => 'bail|required|max:255',
            ], $message);

            $data['reason'] = $request->input('reason');
            $data['is_through'] = $request->input('through');
            try {
                ApplyManage::auditMaterialApply($id, $data);
            } catch (Exception $e) {
                return $this->logException($e);
            }

            return response()->json(['msg' => 'ok', 'data' => '1', 'code' => 1]);
        }
    }

    private function checkProcurement(Request $request)
    {
        $message = [
            'number.required' => '编号必须填写',
            'number.numeric' => '编号必须是数字',
            'number.digits_between' => '编号最多到99999',
            'name.required' => '材料名称必须填写',
            'name.max' => '材料名称最多30个字符',
            'category.required' => '材料类别必须填写',
            'url.required' => '网址必须填写',
            'url.max' => '网址最多50个字符',
            'url.url' => '网址必须是网址格式',
            'unit.required' => '单位必须填写',
            'specification.required' => '规格必须填写',
            'specification.max' => '规格最多50个字符',
            'purchase.required' => '采购数量必须填写',
            'purchase.numeric' => '采购数量必须是数字',
            'purpose.required' => '用途必须填写',
            'purpose.max' => '用途最多255个字符',
            'project.required' => '项目类别必须填写',
            'project.numeric' => '项目类别必须是数字',
            'type.required' => '申请类别必须填写',
            'type.numeric' => '申请类别必须是数字',
        ];
        $this->validate($request, [
            'number' => 'bail|required|numeric|digits_between:1,99999',
            'name' => 'bail|required|max:30',
            'category' => 'bail|required|integer|min:1',
            'url' => 'bail|required|url|max:50',
            'unit' => 'bail|required',
            'specification' => 'bail|required|max:50',
            'purchase' => 'bail|required|numeric|min:1',
            'purpose' => 'bail|required|max:255',
            'project' => 'bail|required|numeric',
            'type' => 'bail|required|numeric',
        ], $message);

        $data['name'] = $request->input('name');
        $data['number'] = $request->input('number');
        $data['category_id'] = $request->input('category');
        $data['url'] = $request->input('url');
        $data['unit'] = $request->input('unit');
        $data['specification'] = $request->input('specification');
        $data['amount'] = $request->input('amount');
        $data['purpose'] = $request->input('purpose');
        $data['project_id'] = $request->input('project');
        $data['type'] = $request->input('type');
        $data['user_id'] = 33;


        return $data;
    }

    private function checkRecipients(Request $request)
    {
        $message = [
            'number.required' => '编号必须填写',
            'number.numeric' => '编号必须是数字',
            'number.digits_between' => '编号最多到99999',
            'name.required' => '材料名称必须填写',
            'name.max' => '材料名称最多30个字符',
            'category.required' => '材料类别必须填写',
            'url.required' => '网址必须填写',
            'url.max' => '网址最多50个字符',
            'url.url' => '网址必须是网址格式',
            'unit.required' => '单位必须填写',
            'specification.required' => '规格必须填写',
            'specification.max' => '规格最多50个字符',
            'amount.required' => '数量必须填写',
            'amount.numeric' => '数量必须是数字',
            'purpose.required' => '用途必须填写',
            'purpose.max' => '用途最多255个字符',
            'project.required' => '项目类别必须填写',
            'project.numeric' => '项目类别必须是数字',
            'type.required' => '申请类别必须填写',
            'type.numeric' => '申请类别必须是数字',
        ];
        $this->validate($request, [
            'number' => 'bail|required|numeric|digits_between:1,99999',
            'name' => 'bail|required|max:30',
            'category' => 'bail|required|integer|min:1',
            'url' => 'bail|required|url|max:50',
            'unit' => 'bail|required',
            'specification' => 'bail|required|max:50',
            'amount' => 'bail|required|numeric|min:1',
            'purpose' => 'bail|required|max:255',
            'project' => 'bail|required|numeric',
            'type' => 'bail|required|numeric',
        ], $message);

        $data['name'] = $request->input('name');
        $data['number'] = $request->input('number');
        $data['category_id'] = $request->input('category');
        $data['url'] = $request->input('url');
        $data['unit'] = $request->input('unit');
        $data['specification'] = $request->input('specification');
        $data['amount'] = $request->input('amount');
        $data['purpose'] = $request->input('purpose');
        $data['project_id'] = $request->input('project');
        $data['type'] = $request->input('type');
        $data['user_id'] = 33;


        return $data;
    }
}


