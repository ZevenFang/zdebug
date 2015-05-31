<?php
namespace Home\Controller;
use Think\Controller;

class BackController extends Controller
{
    /**
     * 修改密码，响应为0代表旧密码错误，为1代表修改成功，为2代表修改失败
     */
    public function resetPwd(){
        if(!session("?user"))
            exit();
        $user = M('user');
        $where['account'] = $_POST['account'];
        $info = $user->where($where)->find();
        if($info['password']==md5($_POST['oldPwd'])){
            $data['password'] = md5($_POST['password']);
            echo $user->where($where)->save($data)?1:2;
        }else echo 0;
    }

    /**
     * 登录响应，返回0代表账号或密码错误，返回1登陆成功，2代表未通过验证用户，3代表用户被删除
     */
    public function login(){
        $where['account'] = $_POST['account'];
        $where['password'] = md5($_POST['password']);
        $user = M('user')->where($where)->find();
        if($user){
            if(!$user['verified'])
                echo 2;
            else if($user['deleted'])
                echo 3;
            else{
                if(isset($_POST['remember'])){
                    cookie('account',$_POST['account']);
                    cookie('password',$_POST['password']);
                }else{
                    cookie('account',null);
                    cookie('password',null);
                }
                unset($user['password']);
                $team = M('team')->where("id={$user['team']}")->find();
                $dept = M('dept')->where("id={$user['dept']}")->find();
                if($user['permission']==3){
                    $project = M('project')->where(array('creator'=>$user['id'],'finished'=>0))->find();
                    if($project)
                        session('project',$project);
                }
                else if($team) {
                    $project = M('project')->where(array('id'=>$team['project'],'finished'=>0))->find();
                    session('project',$project);
                }else
                    session('project',null);
                $data['ip'] = $_SERVER["REMOTE_ADDR"];
                $data['last'] = time();
                M('user')->where($where)->save($data);
                unset($user['password']);
                session('user',$user);
                session('team',$team);
                session('dept',$dept);
                echo 1;
            }
        }else echo 0;
    }

    /**
     * 注册用户，返回1代表添加成功，为0代表添加失败，为2代表已存在该用户名，为3代表验证码错误
     */
    function register(){
        $find = M('user')->where(array('account'=>$_POST['account']))->select();
        if(!empty($find))
            exit('2');
        $verify = new \Think\Verify();
        if(!$verify->check($_POST['verify']))
            exit('3');
        $data = $_POST;
        unset($data['repwd']);
        $data['password'] = md5($data['password']);
        $data['last'] = time();
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        echo M('user')->add($data)?1:0;
    }

    public function avatar(){
        $user = session('user');
        if(!$user)
            $this->redirect('Index/index');
        $config = array(
            'maxSize'    =>    3145728,
            'savePath'   =>    './Public/Uploads/',
            'saveName'   =>    array('uniqid',''),
            'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub'    =>    true,
            'subName'    =>    array('date','Ym/d'),
        );
        $upload = new \Think\Upload($config);// 实例化上传类
        // 上传单个文件
        $info   =   $upload->uploadOne($_FILES['avatar']);
        if(!$info) {// 上传错误提示错误信息
            $msg = $upload->getError();
            redirect(U('Home/Index/msg/msg/'.$msg));
        }else{// 上传成功 获取上传文件信息
            $msg = $info['savepath'].$info['savename'];
            $image = new \Think\Image();
            $msg = substr($msg,1);
            $msg = "./Uploads".$msg;
            $image->open($msg);
            // 生成一个居中裁剪为59*59的缩略头像并保存
            $image->thumb(59, 59,\Think\Image::IMAGE_THUMB_CENTER)->save($msg);
            $msg = substr($msg,1);
            M('user')->where(array('id'=>$user['id']))->save(array('avatar'=>$msg));
            $user['avatar'] = $msg;
            session('user',$user);
            redirect(U('Home/Index/msg/msg/头像设置成功'));
        }
    }

    /**
     * 修改账户信息，成功修改返回1，否则返回0
     */
    public function modInfo(){
        if(!session("?user"))
            exit();
        $user = M('user');
        $where['account'] = $_POST['account'];
        unset($_POST['account']);
        echo $user->where($where)->save($_POST)?1:0;
    }

    /**
     * 退出登录
     */
    public function logout(){
        session('[destroy]');
        $this->redirect('Index/index');
    }

    /**
     * 创建新项目，为0代表有未完成的项目，为1代表创建成功，为2代表创建失败
     */
    public function newProject(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $project = M('project')->where("creator={$user['id']}")->find();
        if(session('?project')||$project&&$project['finished']==0)
            exit('0');
        $data = $_POST;
        $data['creator'] = $user['id'];
        $data['begin'] = time();
        $data['end'] = time()+$_POST['avail']*24*60*60;
        $id = M('project')->add($data);
        if(!$id)
            exit('2');
        $project = M('project')->where("id={$id}")->find();
        session('project',$project);
        exit('1');
    }

    /**
     * 创建任务，为0代表当前没有项目或者项目已经截止无法分配任务，成功时返回插入任务表的id编号
     */
    public function newTask(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $project = M('project')->where("creator={$user['id']}")->find();
        if(!$project||$project['finished']==1)
            exit('0');
        $data = $_POST;
        $data['assigner'] = $user['id'];
        $data['project'] = $project['id'];
        $data['creator'] = $user['id'];
        $data['createTime'] = time();
        echo M('task')->add($data);
    }

    /**
     * 修改项目信息，返回1代表修改成功，返回0代表修改失败
     */
    public function modProject(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $project = session('project');
        $data = $_POST;
        $data['end'] = $project['createTime']+$_POST['avail']*24*60*60;
        echo M('project')->where("id={$project['id']}")->save($data)?1:0;
        session('project', M('project')->find($project['id']));
    }

    /**
     * 分配任务，返回1代表分配成功，返回0代表分配失败
     */
    public function assignTask() {
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $data = $_POST;
        unset($data['id']);
        $data['assigner'] = $user['id'];
        $data['assignedTo'] = M('team')->where(array('id'=>$_POST['id']))->getField('leader');
        echo M('task')->where("id={$_POST['id']}")->save($data)?1:0;
    }

    /**
     * 分配bug，返回1代表分配成功，返回0代表分配失败
     */
    public function assignBug(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=2&&$user['permission']!=3)
            exit();
        $data = $_POST;
        $data['assigner'] = $user['id'];
        echo M('bug')->where("id={$_GET['id']}")->save($data)?1:0;
    }

    /**
     * 查询接管当前项目的团队的信息，返回json格式数据
     */
    public function getTeams(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $project = session('project');
        $teams = array();
        if($project)
            $teams = M('team')->where("project={$project['id']}")->select();
        echo json_encode($teams);
    }

    /**
     * 查询可以被分配的人的信息，返回json格式数据
     */
    public function getAssigner(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=2&&$user['permission']!=3)
            exit();
        $project = session('project');
        $assigners = array();
        if($user['permission']==2){
            $assigners = M('user')->where("team={$user['team']}")->select();
        }else if($user['permission']==3){
            $teams = M('team')->where("project={$project['id']}")->select();
            foreach ($teams as $team){
                $assigner = M('user')->where("team={$team['id']}")->select();
                $assigners = array_merge($assigners,$assigner);
            }
        }
        echo json_encode($assigners);
    }

    /**
     * 提交bug，成功返回1，否则返回0
     */
    public function commitBug(){
        if(!session("?user"))
            exit();
        $project = session('project');
        $user = session('user');
        if($user['permission']>3)
            exit();
        $data = $_POST;
        $data['project'] = $project['id'];
        $data['createTime'] = time();
        $data['creator'] = $user['id'];
        echo M('bug')->add($data)?1:0;
    }

    /**
     * 解决bug，返回1代表状态改变成功，为0代表修改失败
     */
    public function fireBug(){
        if(!session("?user"))
            exit();
        $user = session("user");
        if($user['permission']>3)
            exit();
        $data['finished'] = 1;
        $data['finishTime'] = time();
        $data['solver'] = $user['id'];
        echo M('bug')->where("id={$_GET['id']}")->save($data)?1:0;
    }

    /**
     * 解决任务，返回1代表状态改变成功，为0代表修改失败
     */
    public function fireTask(){
        if(!session("?user"))
            exit();
        $user = session("user");
        if($user['permission']>3)
            exit();
        $data['finished'] = 1;
        $data['finishTime'] = time();
        $data['solver'] = $user['id'];
        echo M('task')->where("id={$_GET['id']}")->save($data)?1:0;
    }

    /**
     * 驳回任务，成功改变状态返回1，否则 返回0
     */
    public function rejectTask(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $data['remark'] = $_POST['remark'];
        $data['finished'] = 0;
        echo M('task')->where("id={$_POST['id']}")->save($data)?1:0;
    }

    /**
     * 驳回bug，成功改变状态返回1，否则 返回0
     */
    public function rejectBug(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=2&&$user['permission']!=3)
            exit();
        $data['remark'] = $_POST['remark'];
        $data['finished'] = 0;
        echo M('bug')->where("id={$_POST['id']}")->save($data)?1:0;
    }

    /**
     * 创建团队，返回1代表创建成功，为0代表创建失败，为2代表编号或名称重复
     */
    public function newTeam(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $where['code'] = $_POST['code'];
        $where['name'] = $_POST['name'];
        $where['_logic'] = 'OR';
        $find = M('team')->where($where)->select();
        if(!empty($find))
            exit('2');
        $project = session('project');
        $_POST['mates'][] = $_POST['leader'];
        $where['id'] = array('in',$_POST['mates']);
        $data['name'] = $_POST['name'];
        $data['code'] = $_POST['code'];
        $data['leader'] = $_POST['leader'];
        $data['createTime'] = time();
        $data['project'] = $project['id'];
        $team = M('team')->add($data);
        if($team) {
            M('user')->where(array('id'=>$data['leader']))->save(array('permission'=>2));
            echo M('user')->where($where)->save(array('team' => $team)) ? 1 : 0;
        }
    }

    /**
     * 创建团队，返回1代表创建成功，为0代表创建失败，为2代表编号或名称重复
     */
    public function newDept(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $where['code'] = $_POST['code'];
        $where['name'] = $_POST['name'];
        $where['_logic'] = 'OR';
        $find = M('dept')->where($where)->select();
        if(!empty($find))
            exit('2');
        $_POST['mates'][] = $_POST['manager'];
        $where['id'] = array('in',$_POST['mates']);
        $data['name'] = $_POST['name'];
        $data['code'] = $_POST['code'];
        $data['address'] = $_POST['address'];
        $data['phone'] = $_POST['phone'];
        $data['manager'] = $_POST['manager'];
        $dept = M('dept')->add($data);
        if($dept)
            echo M('user')->where($where)->save(array('dept'=>$dept))?1:0;
        else
            M('dept')->delete($dept);
    }

    /**
     * 增加用户，返回1代表添加成功，为0代表添加失败，为2代表已存在该用户名
     */
    function newUser(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $find = M('user')->where(array('account'=>$_POST['account']))->select();
        if(!empty($find))
            exit('2');
        $data = $_POST;
        unset($data['repwd']);
        $data['password'] = md5($data['password']);
        $data['last'] = time();
        $data['ip'] = $_SERVER["REMOTE_ADDR"];
        $data['verified'] = 1;
        echo M('user')->add($data)?1:0;
    }

    /**
     * 解散团队，解散成功返回1，否则返回0
     */
    public function disTeam(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $where['team'] = $_GET['id'];
        M('user')->where($where)->save(array('team' => 0));
        echo M('team')->delete($_GET['id'])?1:0;
    }

    /**
     * 解散部门，解散成功返回1，否则返回0
     */
    public function disDept(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $where['dept'] = $_GET['id'];
        M('user')->where($where)->save(array('dept' => 0));
        echo M('dept')->delete($_GET['id'])?1:0;
    }

    /**
     * 改变团队的队长，成功修改返回1,否则返回0
     */
    public function changeLeader(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $leader = M('team')->where("id={$_GET['team']}")->getField('leader');
        M('user')->where(array('id'=>$leader))->save(array('permission'=>1));
        M('user')->where(array('id'=>$_POST['id']))->save(array('permission'=>2));
        echo M('team')->where("id={$_GET['team']}")->save($_POST)?1:0;
    }

    /**
     * 解散一个团队成员，成功返回1,否则返回0
     */
    public function disMember(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $data['team'] = 0;
        echo M('user')->where(array('id'=>$_GET['id']))->save($data)?1:0;
    }

    /**
     * 删除一个用户，将用户放进回收站，成功执行返回1,否则返回0
     */
    public function deleteUser(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $u = M('user')->where($_GET)->find();
        if($u['permission']==4&&$user['permission']!=5)
            exit();
        echo M('user')->where($_GET)->save(array('deleted'=>1))?1:0;

    }

    /**
     * 彻底删除一个用户，将用户从回收站中删除，成功执行返回1,否则返回0
     */
    public function deleteUserComp(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        echo M('user')->delete($_GET['id'])?1:0;
    }

    /**
     * 添加团队新成员，全部添加成功返回1,否则返回0
     */
    public function newMember(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']!=3)
            exit();
        $where['id'] = array('in',$_POST['mates']);
        echo  M('user')->where($where)->save(array('team'=>$_GET['team']))?1:0;
    }

    /**
     * 还原用户，成功返回1,否则返回0
     */
    public function restoreUser(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $data['deleted'] = 0;
        $data['team'] = 0;
        $data['dept'] = $_GET['dept'];
        $data['permission'] = $_GET['permission'];
        echo M('user')->where(array('id'=>$_GET['id']))->save($data)?1:0;
    }

    /**
     * 修改用户权限，成功返回1,否者返回0
     */
    public function modPermission(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $data['permission'] = $_GET['permission'];
        $data['team'] = 0;
        echo M('user')->where(array('id'=>$_GET['id']))->save($data)?1:0;
    }

    /**
     * 改变部门主管，成功修改返回1,否则返回0
     */
    public function changeManager(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        echo M('dept')->where("id={$_GET['dept']}")->save($_POST)?1:0;
    }

    /**
     * 解散一个成员，成功返回1,否则返回0
     */
    public function disDeptMember(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $data['dept'] = 0;
        echo M('user')->where(array('id'=>$_GET['id']))->save($data)?1:0;
    }

    /**
     * 添加部门新成员，全部添加成功返回1,否则返回0
     */
    public function newDeptMember(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $where['id'] = array('in',$_POST['mates']);
        echo  M('user')->where($where)->save(array('dept'=>$_GET['dept']))?1:0;
    }

    /**
     * 新建文档,已存在该文档标题返回2,成功返回1,否则返回0
     */
    public function newDoc(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']>3)
            exit();
        $project = session('project');
        $where['project'] = $project['id'];
        $where['name'] = $_POST['name'];
        $find = M('doc')->where($where)->select();
        if(!empty($find))
            exit('2');
        $user = session('user');
        $data = $_POST;
        $data['project'] = $project['id'];
        $data['addedBy'] = $user['id'];
        $data['addedTime'] = time();
        echo M('doc')->add($data)?1:0;
    }
    /**
     * 编辑文档,已存在该文档标题返回2,成功返回1,否则返回0
     */
    public function editDoc(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']>3)
            exit();
        $name = M('doc')->where("id={$_GET['id']}")->getField('name');
        $project = session('project');
        $where['project'] = $project['id'];
        $where['name'] = $_POST['name'];
        $find = array();
        if($name!=$_POST['name'])//文档名称有改变时再检测
            $find = M('doc')->where($where)->select();
        if(!empty($find))
            exit('2');
        $data = $_POST;
        $data['editedBy'] = $user['id'];
        $data['editedTime'] = time();
        echo M('doc')->where("id={$_GET['id']}")->save($data)?1:0;
    }
    /**
     * 拒绝用户验证并删除该用户，成功返回1,否则返回0
     */
    function rejectVerify(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        echo M('user')->delete($_GET['id'])?1:0;
    }
    /**
     * 拒绝用户验证并删除该用户，成功返回1,否则返回0
     */
    function userVerify(){
        if(!session("?user"))
            exit();
        $user = session('user');
        if($user['permission']<4)
            exit();
        $data = $_POST;
        $data['verified'] = 1;
        echo M('user')->where(array('id'=>$_GET['id']))->save($data)?1:0;
    }
}

?>