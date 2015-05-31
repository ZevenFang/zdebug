<?php
namespace Home\Controller;

use Think\Controller;
use Org\Util\Ueditor;

class PluginController extends Controller
{
    public function verify(){
        $Verify = new \Think\Verify();
        $Verify->entry();
    }
    public function ueditor(){
        $data = new Ueditor();
        echo $data->output();
    }

}

?>