<?php
namespace App\Http\Controllers\Admin\V1;

use Exception;
use App\Models\V1\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/supplier 供货商 - 列表
     * @apiName supplier_list
     * @apiGroup Web_Supplier
     *
     * @apiParam {Integer} [p=1]  可选,供货商信息页码
     * @apiParam {Integer} [n=10] 可选,每页显示数量,上限100
     *
     * @apiParam {String}  [name]  搜索 供货商名称
     *
     * @apiSuccess {Integer} msg  供货商总数
     * @apiSuccess {Object}  data 供货商列表
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
                "msg": 2,
                "data": [
                {
                "name": "光明",
                "contacts": "老王",
                "phone": "13888747478",
                "url": "http://baudi.com",
                "address": "文三路90号",
                "account": "隔壁老王",
                "bank": "杭州银行",
                "branch": "文三支行",
                "card": "123132132"
                },
                {
                "name": "王老吉",
                "contacts": "老王",
                "phone": "13888747478",
                "url": "http://baudi.com",
                "address": "文三路90号",
                "account": "隔壁老王",
                "bank": "杭州银行",
                "branch": "文三支行",
                "card": "123132132"
                }
                ],
                "code": 1
                }
     *
     *
     * @apiSampleRequest http://localhost/blo/web/v1/supplier
     */
    public function index(Request $request)
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
            list($total, $data) = Supplier::listSupplier($page, $num, $name);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => $total, 'data' => $data, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {get} web/v1/supplier/:id 供货商 - 查看
     * @apiName supplier_select
     * @apiGroup Web_Supplier
     *
     *
     * @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
            "msg": "ok",
            "data": {
            "id": 9,
            "name": "光明",
            "contacts": "老王",
            "phone": "13888747478",
            "url": "http://baudi.com",
            "address": "文三路90号",
            "price": null,
            "account": "隔壁老王",
            "bank": "杭州银行",
            "branch": "文三支行",
            "card": "123132132",
            "create_at": 1465182875,
            "update_at": null,
            "is_del": "0"
            },
            "code": 1
        }
     *
     *
     * @apiSampleRequest http://localhost/blog/web/v1/supplier/:id
     */
    public function select($id)
    {
        try {
            $data = Supplier::selectSupplierById($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $data, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {post} web/v1/supplier 供货商 - 创建
     * @apiName supplier_create
     * @apiGroup Web_Supplier
     *
     * @apiParam {String}   name    供货商
     * @apiParam {String}   contacts    供货商联系人
     * @apiParam {String}   phone  供货商联系电话
     * @apiParam {String}   url  供货商URL
     * @apiParam {String}   address  供货商地址
     * @apiParam {String}   account     收款账户
     * @apiParam {String}   bank       银行
     * @apiParam {String}   branch     支行
     * @apiParam {String}   card       卡号
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
     * @apiSampleRequest http://localhost/blog/web/v1/supplier
     */
    public function create(Request $request)
    {
        $data = $this->check($request);
        try {
            $id = Supplier::createSupplier($data);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    /**
     * @apiVersion 1.0.0
     *
     * @api {delete} web/v1/supplier/:id 供货商 - 删除
     * @apiName supplier_delete
     * @apiGroup Web_Supplier
     *
     *
     @apiSuccessExample Success-Response:
     *   HTTP/1.1 200 OK
     *   {
     *     "msg": "ok",
     *     "data": 1,
     *     "code": 1
     *   }
     *
     *
     * @apiSampleRequest http://localhost/blo/web/v1/supplier/:id
     */
    public function delete($id)
    {
        try {
            Supplier::deleteSupplier($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $id, 'code' => 1]);
    }

    private function check(Request $request)
    {
        $message = [
            'name.required' => '供货商名称必须填写',
            'name.max' => '供货商名称最多30个字符',
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
        ];
        $this->validate($request, [
            'name' => 'bail|required|max:30',
            'contacts' => 'bail|required|max:30',
            'phone' => 'bail|required|max:16',
            'url' => 'bail|required|url|max:50',
            'address' => 'bail|required|max:100',
            'account' => 'bail|required|max:30',
            'bank' => 'bail|required|max:50',
            'branch' => 'bail|required|max:50',
            'card' => 'bail|required|numeric|digits_between:5,50',
        ], $message);

        $data['name'] = $request->input('name');
        $data['contacts'] = $request->input('contacts');
        $data['phone'] = $request->input('phone');
        $data['url'] = $request->input('url');
        $data['address'] = $request->input('address');
        $data['account'] = $request->input('account');
        $data['bank'] = $request->input('bank');
        $data['branch'] = $request->input('branch');
        $data['card'] = $request->input('card');

        $data['name'] = preg_replace('/\'/', '', $data['name']);

        return $data;
    }


}