<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\V1\OrdersManage;
use App\Models\V1\StorageMaterial;

class PurchaseManageController extends Controller
{

    public function index(Request $request)
    {

    }

    public function createOrders(Request $request)
    {
        $data = $this->checkOrders($request);

        try {
            $id = OrdersManage::createOrders($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);

    }

    public function createStorage(Request $request)
    {
        $data = $this->checkStorage($request);

        try {
            $id = StorageMaterial::createStorage($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/purchase/storage 入库 - 列表
     * @apiName storage_list
     * @apiGroup Web
     *
     * @apiParam {Integer} [p=1]  可选,元素信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量,上限100
     *
     * @apiParam {String}  [name]  搜索 材料名称
     *
     * @apiSuccess {Integer} msg  入库总数
     * @apiSuccess {Object}  data 入库列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
            "msg": 1,
            "data": [
                {
                    "stoId": 6,
                    "number": 2,
                    "url": "http://baudi.com",
                    "categoryName": "办公品",
                    "specification": "BAM型",
                    "amount": 100,
                    "unit": "个",
                    "projectName": "大风",
                    "userId": 43,
                    "createAt": 1465368351,
                    "invoice": 1,
                    "reimburse": null,
                    "storage": 0
                }
            ],
            "code": 1
        }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/purchase/storage
     */
    public function listStorage(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1|max:100',
            'name' => 'bail|filled|string|max:10',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $name = $request->input('name', '');

        try {
            list($total, $data) = StorageMaterial::listStorage($page, $num, $name);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>$total, 'data'=>$data, 'code'=>1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/material/purchase/reimburse 报销 - 列表
     * @apiName storage_list
     * @apiGroup Web
     *
     * @apiParam {Integer} [p=1]  可选,元素信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量,上限100
     *
     * @apiParam {Integer}  [start]  搜索 开始时间
     * @apiParam {Integer}  [end]  搜索 结束时间
     * @apiParam {Integer}  [project]  搜索 项目id
     *
     * @apiSuccess {Integer} msg  入库总数
     * @apiSuccess {Object}  data 入库列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     * {
        "msg": 1,
        "data": [
            {
                "stoId": 6,
                "number": 2,
                "categoryName": "办公品",
                "specification": "BAM型",
                "amount": 100,
                "unit": "个",
                "price": 9.9,
                "projectName": "大风",
                "time": null,
                "invoice": 1,
                "reimburse": 1,
                "storage": 0
            }
        ],
        "code": 1
        }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/purchase/reimburse
     */
    public function listReimburse(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1|max:100',
            'start' => 'bail|filled|integer',
            'end' => 'bail|filled|integer',
            'project' => 'bail|filled|integer',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $start = $request->input('start', '');
        $end = $request->input('end', '');
        $project = $request->input('project', '');

        try {
            list($total, $data) = StorageMaterial::listReimburse($page, $num, $start, $end, $project);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>$total, 'data'=>$data, 'code'=>1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/material/purchase/reimburse 报销 - 确认
     * @apiName reimburse_update
     * @apiGroup Web
     *
     * @apiParam {Integer} [reimburse]  报销

     * @apiSuccess {Integer} msg  ok
     * @apiSuccess {Integer}  data id
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
     *
     * @apiSampleRequest http://localhost/blog/web/v1/material/purchase/storage
     */
    public function reimburseStorage(Request $request, $id)
    {
        $message = [

        ];
        $this->validate($request, [
            'reimburse' => 'bail|required|integer',
        ], $message);

        $data['is_reimburse'] = $request->input('reimburse');

        try {
            StorageMaterial::updateStorage($id, $data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>'ok', 'data'=>$id, 'code'=>1]);
    }

    private function checkOrders(Request $request)
    {
        $message = [
            'supName.required' => '供货商名称必须填写',
            'supName.max' => '供货商名称最多30个字符',
            'contacts.required' => '联系人必须填写',
            'contacts.regex' => '联系人中文名称不合法',
            'phone.required' => '联系电话必须填写',
            'phone.max' => '联系电话最多16个字符',
            'url.required' => '网址必须填写',
            'url.max' => '网址最多50个字符',
            'url.url' => '网址必须是网址格式',
            'address.required' => '地址必须填写',
            'address.max' => '地址最多100个字符',
            'account.required' => '收款账户必须填写',
            'account.max' => '收款账户最多30个字符',
            'bank.required' => '开户行必须填写',
            'bank.max' => '开户行最多50个字符',
            'branch.required' => '支行必须填写',
            'branch.max' => '支行最多50个字符',
            'card.required' => '卡号必须填写',
            'card.numeric' => '卡号必须是数字',
            'card.digits_between' => '卡号最多50个字符',
            'price.required' => '价格必须填写',
            'price.numeric' => '价格必须是数字',
            'price.between' => '价格区间为0~9999999.99',
            'express.required' => '运单号必须填写',
            'express.max' => '运单号最多50个字符',
            'invoice.required' => '有无发票必须填写',
        ];
        $this->validate($request, [
            'supName' => 'bail|required|max:30',
            'contacts' => 'bail|required|max:30',
            'phone' => 'bail|required|max:16',
            'url' => 'bail|required|url|max:50',
            'address' => 'bail|required|max:100',
            'account' => 'bail|required|max:30',
            'bank' => 'bail|required|max:50',
            'branch' => 'bail|required|max:50',
            'card' => 'bail|required|numeric|digits_between:5,50',
            'price' => 'bail|required|numeric|between:0,9999999.99',
            'express' => 'bail|required|max:50',
            'invoice' => 'bail|required|integer',
            'apply' => 'bail|required|integer',
        ], $message);

        $data['sup_name'] = $request->input('supName');
        $data['contacts'] = $request->input('contacts');
        $data['phone'] = $request->input('phone');
        $data['url'] = $request->input('url');
        $data['address'] = $request->input('address');
        $data['account'] = $request->input('account');
        $data['bank'] = $request->input('bank');
        $data['branch'] = $request->input('branch');
        $data['card'] = $request->input('card');
        $data['price'] = $request->input('price');
        $data['express_no'] = $request->input('express');
        $data['is_invoice'] = $request->input('invoice');
        $data['apply_id'] = $request->input('apply');
        $data['user_id'] = 43;

        return $data;
    }

    private function checkStorage(Request $request)
    {
        $message = [
            'amount.required' => '数量必须填写',
            'amount.numeric' => '数量必须是数字',
            'url.required' => '网址必须填写',
            'url.max' => '网址最多50个字符',
            'url.url' => '网址必须是网址格式',
            'invoice.required' => '有无发票必须填写',
        ];
        $this->validate($request, [
            'amount' => 'bail|required|numeric|min:1',
            'url' => 'bail|required|url|max:50',
            'invoice' => 'bail|required|integer',
            'orders' => 'bail|required|integer',
        ], $message);

        $data['amount'] = $request->input('amount');
        $data['url'] = $request->input('url');
        $data['is_invoice'] = $request->input('invoice');
        $data['orders_id'] = $request->input('orders');
        $data['user_id'] = 43;

        return $data;
    }


}