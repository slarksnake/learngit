<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Slark;

class IndexController extends Controller
{
    public function index()
    {
        $message = [

        ];
        var_dump($message);
        $test = Slark::where('hero', 'slark')->get();
        return $this->createJson($test);
    }
}
