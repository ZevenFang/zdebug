<?php
namespace Home\Controller;
use Think\Controller;

class InstallController extends Controller
{

    function index()
    {
        $this->display();
    }

    function step1()
    {
        $this->display();
    }

    function step2()
    {
        $conn = @mysql_connect($_POST['host'].':'.$_POST['port'],$_POST['u'],$_POST['p']);
        if(empty($conn)){
			header("Content-Type: text/html; charset=utf-8");
            die('<h1>数据库连接失败！</h1>');
		}
        mysql_query("set names utf8");
        mysql_query("create database {$_POST['dbname']}");
        mysql_query("use {$_POST['dbname']}");
        $sql = file_get_contents(APP_PATH.'Home/Common/zdebug.sql');
        $sqls = explode(';',$sql);
        foreach($sqls as $sql)
            mysql_query($sql);
        $data['realname'] = $_POST['realname'];
        $data['account'] = $_POST['admin'];
        $data['password'] = md5($_POST['pwd']);
        $data['sex'] = $_POST['sex'];
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        $data['last'] = time();
		$sql = "INSERT INTO zd_user(id,account,password,realname,sex,verified,ip,last,permission)VALUES(1,'".$data['account']."','".$data['password']."','".$data['realname']."',{$data['sex']},1,'".$data['ip']."',{$data['last']},5)";
        mysql_query($sql);
        mysql_close($conn);
        $config = array(
            'DB_HOST'=>$_POST['host'],
            'DB_NAME'=>$_POST['dbname'],
            'DB_USER'=>$_POST['u'],
            'DB_PWD'=>$_POST['p'],
            'DB_PORT'=>$_POST['port'],
            'installed'=>true
        );
        $this->update_config($config);
        $this->display();
    }

    public function header(){
        $this->display();
    }

    public function footer(){
        $this->display();
    }

    protected function update_config($new_config, $config_file = './Application/Home/Conf/config.php') {
        if (is_writable($config_file)) {
            $config = require $config_file;
            $config = array_merge($config, $new_config);
            file_put_contents($config_file, "<?php \nreturn " . stripslashes(var_export($config, true)) . ";", LOCK_EX);
            @unlink(RUNTIME_FILE);
            return true;
        } else {
            return false;
        }
    }

}