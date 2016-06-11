<?php
namespace App\Http\Controllers\Admin\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use App\Models\V1\Material;
use App\Models\V1\StorageMaterial;
use App\Models\V1\ApplyManage;

class InverntoryManage extends Controller
{
    public function index(Request $request)
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1',
            'supplier' => 'bail|filled|number',
            'name' => 'bail|filled|string|max:10',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $name = $request->input('name', '');
        $supplier = $request->input('supplier', '');

        try {
            list($total, $data) = Material::listMaterial($page, $num, $name, $supplier);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => $total, 'data' => $data, 'code' => 1]);
    }

    public function select($id)
    {
        try {
            $data = Material::selectMaterialById($id);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg' => 'ok', 'data' => $data, 'code' => 1]);
    }
    
    public function storageList()
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
    
    public function recipientsList()
    {
        $message = [

        ];
        $this->validate($request, [
            'p' => 'bail|filled|integer|min:1',
            'n' => 'bail|filled|integer|min:1|max:100',
            'name' => 'bail|filled|string|max:10',
            'project' => 'bail|filled|integer|max:10',
            'userId' => 'bail|filled|integer|max:10',
        ], $message);

        $page = $request->input('p', 1);
        $num = $request->input('n', 10);
        $name = $request->input('name', '');
        $project = $request->input('project', '');
        $userId = $request->input('userId','');
        $type = 1;
        $state = 2;
        try {
            list($total, $data) = ApplyManage::searchMaterialApply($page, $num, $type, $state, $name, $project, $userId);
        } catch (Exception $e) {
            return $this->logException($e);
        }

        return response()->json(['msg'=>$total, 'data'=>$data, 'code'=>1]);
    }
    
    public function confirmRecipients($id)
    {
        
    }
}
