<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    /**
     * 登陆界面
     */
    public function index(){
        if(!C('installed'))
            redirect(U('Install/index'));
        $this->assign('account',cookie('account'));
        $this->assign('password',cookie('password'));
        $this->assign('link','css');
        $this->display();
    }

    /**
     * 注册页面
     */
    public function register(){
        $this->display();
    }

    /**
     * 设置头像
     */
    public function avatar(){
        if(!session('?user'))
            $this->redirect('index');
        $this->assign('user',session('user'));
        $this->display();
    }

    /**
     * 个人中心界面
     */
    public function dashboard(){
        if(!session('?user'))
            $this->redirect('index');
        $this->assign('dash','-active');
        $user = session('user');
        $team = session('team');
        $project = session('project');
        $dept = session('dept');
        if($dept)
            $manager = M('user')->where("id={$dept['manager']}")->find();
        if($team){
            $leader = M('user')->where("id={$team['leader']}")->find();
            $teammates = M('user')->where("team={$team['id']} and id!={$user['id']}")->select();
        }
        $temp = M('task')->where(array('assignedTo'=>$user['id'],'deleted'=>0))->select();
        foreach ($temp as $task){
            $task['assigner'] = M('user')->where("id={$task['assigner']}")->getField('realname');
            $tasks[] = $task;
        }
        $temp = M('task')->where(array('assigner'=>$user['id'],'deleted'=>0))->select();
        foreach ($temp as $task){
            $task['assignedTo'] = M('team')->where("id={$task['assignedto']}")->getField('name');
            $myTasks[] = $task;
        }
        $temp = M('bug')->where(array('assignedTo'=>$user['id'],'deleted'=>0))->select();
        foreach ($temp as $bug){
            $bug['assigner'] = M('user')->where("id={$bug['assigner']}")->getField('realname');
            $bugs[] = $bug;
        }
        $temp = M('bug')->where(array('assigner'=>$user['id'],'deleted'=>0))->select();
        foreach ($temp as $bug){
            $bug['assignedTo'] = M('user')->where("id={$bug['assignedto']}")->getField('realname');
            $myBugs[] = $bug;
        }
        $this->assign('tasks',$tasks);
        $this->assign('myTasks',$myTasks);
        $this->assign('bugs',$bugs);
        $this->assign('myBugs',$myBugs);
        $this->assign('user',$user);
        $this->assign('team',$team);
        $this->assign('manager',$manager);
        $this->assign('leader',$leader);
        $this->assign('teammates',$teammates);
        $this->assign('project',$project);
        $this->assign('dept',$dept);
        $this->display();
    }

    /**
     * 项目管理界面
     */
    public function projectManagement(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=3)
            $this->redirect('dashboard');
        $this->assign('user',$user);
        $this->assign('flm','-active');
        $this->display();
    }

    /**
     * 项目完结跳转动作
     */
    public function finishProjectAct(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=3)
            $this->redirect('dashboard');
        $teams = M('team')->where(array('project'=>$_GET['id']))->select();
        M('project')->where(array('id'=>$_GET['id']))->save(array('finished'=>1));
        $ids = array();
        foreach ($teams as $team) {
            $ids[] = $team['id'];
            M('team')->delete($team['id']);
        }
        if(!empty($ids)) {
            $where['team'] = array('in', $ids);
            M('user')->where($where)->save(array('team' => 0));
        }
        M('task')->where(array('project'=>$_GET['id']))->save(array('deleted'=>1));
        M('bug')->where(array('project'=>$_GET['id']))->save(array('deleted'=>1));
        session('project',null);
        $this->redirect('dashboard');
    }

    /**
     * 项目管理界面
     */
    public function analytics(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $this->assign('anlt','-active');
        $project = session('project');
        if($project){
            $temp = M('bug')->where("project={$project['id']}")->select();
            foreach ($temp as $bug){
                $creator = M('user')->find($bug['creator']);
                $assignedTo = M('user')->find($bug['assignedto']);
                $solver = M('user')->find($bug['solver']);
                $bug['creator'] = $creator;
                $bug['assignedTo'] = $assignedTo;
                $bug['solver'] = $solver;
                $bug['finished']==0? $bugs[]=$bug : $debugs[]=$bug;
            }
        }
        $this->assign('user',$user);
        $this->assign('project',$project);
        $this->assign('bugs',$bugs);
        $this->assign('debugs',$debugs);
        $this->display();
    }

    /**
     * 团队管理界面
     */
    public function teamManagement(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $project = session('project');
        if($project){
            $temp = M('team')->where("project={$project['id']}")->select();
            foreach ($temp as $team){
                $team['leader'] = M('user')->where("id={$team['leader']}")->getField('realname');
                $teams[] = $team;
            }
            $where['team'] = 0;
            $where['permission'] = array('lt',3);
            $where['deleted'] = 0;
            $where['verified'] = 1;
            $temp = M('user')->where($where)->select();
            foreach ($temp as $mate){
                if($mate['dept']==0)
                    $mate['dept'] = '无';
                else
                    $mate['dept'] = M('dept')->where("id={$mate['dept']}")->getField('name');
                $mates[] = $mate;
            }
            $this->assign('mates',$mates);
            $this->assign('project',$project);
            $this->assign('teams',$teams);
        }
        $this->assign('user',$user);
        $this->assign('icn','-active');
        $this->display();
    }

    /**
     * 部门管理界面
     */
    public function deptManagement(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']<4)
            $this->redirect('dashboard');
        $this->assign('tb','-active');
        $temp = M('dept')->select();
        foreach($temp as $dept){
            $dept['manager'] = M('user')->where(array('id'=>$dept['manager']))->getField('realname');
            $depts[] = $dept;
        }
        $where['dept'] = 0;
        $where['deleted'] = 0;
        $where['verified'] = 1;
        $temp = M('user')->where($where)->select();
        foreach ($temp as $mate){
            if($mate['team']==0)
                $mate['team'] = '无';
            else
                $mate['team'] = M('team')->where("id={$mate['team']}")->getField('name');
            $mates[] = $mate;
        }
        $this->assign('mates',$mates);
        $this->assign('depts',$depts);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 文档管理
     */
    public function fileManagement(){
        if(!session('?user'))
            $this->redirect('index');
        $project = session('project');
        if($project) {
            $where['project'] = $project['id'];
            $where['deleted'] = 0;
            $temp = M('doc')->where($where)->select();
            foreach($temp as $doc){
                $doc['addedby'] = M('user')->where(array('id'=>$doc['addedby']))->getField('realname');
                $doc['editedby'] = M('user')->where(array('id'=>$doc['editedby']))->getField('realname');
                $docs[] = $doc;
            }
            $this->assign('docs',$docs);
        }
        $this->assign('typ','-active');
        $this->assign('project',$project);
        $this->assign('user',session('user'));
        $this->display();
    }

    /**
     * 用户管理界面
     */
    public function userManagement(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']<4)
            $this->redirect('dashboard');
        $this->assign('grid','-active');
        $user = session('user');
        $where['id']=array('neq',$user['id']);
        $where['verified'] = 1;
        $where['deleted'] = 0;
        $where['permission']=4;
        $admins = M('user')->where($where)->select();
        $where['permission']=array('lt',4);
        $temp = M('user')->where($where)->select();
        foreach($temp as $tmp){
            $tmp['dept'] = M('dept')->where(array('id'=>$tmp['dept']))->getField('name');
            $tmp['team'] = M('team')->where(array('id'=>$tmp['team']))->getField('name');
            $users[] = $tmp;
        }
        $users_no_verify = M('user')->where(array('verified'=>0,'deleted'=>0))->select();
        $users_del = M('user')->where(array('deleted'=>1))->select();
        $depts = M('dept')->select();
        $this->assign('admins',$admins);
        $this->assign('users',$users);
        $this->assign('users_no_verify',$users_no_verify);
        $this->assign('users_del',$users_del);
        $this->assign('depts',$depts);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 项目详情信息
     */
    public function projectInfo(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $this->assign('project',session('project'));
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * bug详情信息
     */
    public function bugInfo(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $bug = M('bug')->find($_GET['id']);
        $this->assign('bug',$bug);
        $this->assign('user',$user);
        $this->assign('remark',$_GET['remark']);
        $this->display();
    }

    /**
     * 任务详情信息
     */
    public function taskInfo(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $task = M('task')->find($_GET['id']);
        $this->assign('task',$task);
        $this->assign('user',$user);
        $this->assign('remark',$_GET['remark']);
        $this->display();
    }

    /**
     * 编辑bug信息界面
     */
    public function editBug(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $bug = M('bug')->find($_GET['id']);
        $this->assign('bug',$bug);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 编辑任务信息界面
     */
    public function editTask(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=3)
            $this->redirect('dashboard');
        $task = M('task')->find($_GET['id']);
        $this->assign('task',$task);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 编辑文档界面
     */
    public function editDoc(){
        if(!session('?user'))
            $this->redirect('index');
        $project = session('project');
        $doc = M('doc')->find($_GET['id']);
        if($doc['project']!=$project['id'])
            $this->redirect('fileManagement');
        $this->assign('user',session('user'));
        $this->assign('doc',$doc);
        $this->assign('project',$project);
        $this->display();
    }

    /**
     * 提交编辑bug跳转动作
     */
    public function editBugAct(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        M('bug')->where("id={$_GET['id']}")->save($_POST);
        $this->redirect('analytics');
    }

    /**
     * 编辑任务跳转动作
     */
    public function editTaskAct(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=3)
            $this->redirect('dashboard');
        M('task')->where("id={$_GET['id']}")->save($_POST);
        $this->redirect('dashboard');
    }

    /**
     * 编辑团队信息
     */
    public function editTeam(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=3)
            $this->redirect('dashboard');
        $team = M('team')->find($_GET['id']);
        $temp = M('user')->where("team={$_GET['id']}")->select();
        foreach ($temp as $mate){
            if($mate['dept']==0)
                $mate['dept'] = '无';
            else
                $mate['dept'] = M('dept')->where("id={$mate['dept']}")->getField('name');
            $mates[] = $mate;
        }
        $where['team'] = 0;
        $where['permission'] = array('lt',3);
        $temp = M('user')->where($where)->select();
        foreach ($temp as $mate){
            if($mate['dept']==0)
                $mate['dept'] = '无';
            else
                $mate['dept'] = M('dept')->where("id={$mate['dept']}")->getField('name');
            $frees[] = $mate;
        }
        $this->assign('team',$team);
        $this->assign('mates',$mates);
        $this->assign('frees',$frees);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 编辑部门信息
     */
    public function editDept(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']<4)
            $this->redirect('dashboard');
        $dept = M('dept')->find($_GET['id']);
        $temp = M('user')->where("dept={$_GET['id']}")->select();
        foreach ($temp as $mate){
            if($mate['team']==0)
                $mate['team'] = '无';
            else
                $mate['team'] = M('team')->where("id={$mate['team']}")->getField('name');
            $mates[] = $mate;
        }
        $temp = M('user')->where("dept=0")->select();
        foreach ($temp as $mate){
            if($mate['team']==0)
                $mate['team'] = '无';
            else
                $mate['team'] = M('team')->where("id={$mate['team']}")->getField('name');
            $frees[] = $mate;
        }
        $this->assign('dept',$dept);
        $this->assign('mates',$mates);
        $this->assign('frees',$frees);
        $this->assign('user',$user);
        $this->display();
    }

    /**
     * 解决bug跳转动作
     */
    public function fireBug(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']>3)
            $this->redirect('dashboard');
        $data['finishTime'] = time();
        $data['solver'] = $user['id'];
        $data['finished'] = 1;
        M('bug')->where("id={$_GET['id']}")->save($data);
        $this->redirect('analytics');
    }

    /**
     * 驳回bug跳转动作
     */
    public function rejectBug(){
        if(!session('?user'))
            $this->redirect('index');
        $user = session('user');
        if($user['permission']!=2&&$user['permission']!=3)
            $this->redirect('dashboard');
        $data['remark'] = $_POST['remark'];
        $data['finished'] = 0;
        M('bug')->where("id={$_POST['id']}")->save($data);
        $this->redirect('analytics');
    }

    public function document(){
        if(!session('?user'))
            $this->redirect('index');
        $project = session('project');
        $doc = M('doc')->find($_GET['id']);
        if($doc['project']!=$project['id'])
            $this->redirect('fileManagement');
        $this->assign('user',session('user'));
        $this->assign('doc',$doc);
        $this->assign('project',$project);
        $this->display();
    }

    /**
     * 跳转提示页面
     */
    public function msg(){
        if(!session('?user'))
            $this->redirect('index');
        $this->assign('msg',$_GET['msg']);
        $this->assign('user',session('user'));
        $this->display();
    }

    /**
     * 系统基本模型
     */
    public function base(){
        $this->display();
    }

    /**
     * 系统头部
     */
    public function header(){
        $this->display();
    }

    /**
     * 系统尾部
     */
    public function footer(){
        $this->display();
    }
}