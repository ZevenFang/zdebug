-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost:3306
-- 生成日期: 2015 年 05 月 30 日 13:58
-- 服务器版本: 5.5.20
-- PHP 版本: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `zdebug`
--

-- --------------------------------------------------------

--
-- 表的结构 `zd_bug`
--

CREATE TABLE IF NOT EXISTS `zd_bug` (
  `id` int(10) unsigned NOT NULL,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT 'bug标题',
  `keywords` varchar(45) NOT NULL DEFAULT '' COMMENT '关键词',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'bug类型，1代码错误，2界面优化，3设计缺陷，4安全相关，5性能问题，6其他',
  `finished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否完成',
  `project` int(10) unsigned NOT NULL COMMENT '所属项目',
  `creator` int(10) unsigned NOT NULL COMMENT 'bug发现者',
  `assigner` int(10) unsigned NOT NULL DEFAULT '0',
  `assignedTo` int(10) unsigned NOT NULL COMMENT 'bug被分配的人',
  `solver` int(10) unsigned NOT NULL COMMENT 'bug解决者',
  `step` text NOT NULL COMMENT '产生的步骤',
  `expect` text NOT NULL COMMENT '期望结果',
  `remark` text NOT NULL,
  `createTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `finishTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `deleted` tinyint(3) unsigned NOT NULL COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `fk_zd_bug_zd_project1_idx` (`project`),
  KEY `fk_zd_bug_zd_user1_idx` (`creator`),
  KEY `fk_zd_bug_zd_user2_idx` (`assignedTo`),
  KEY `fk_zd_bug_zd_user3_idx` (`solver`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='bug表';

-- --------------------------------------------------------

--
-- 表的结构 `zd_dept`
--

CREATE TABLE IF NOT EXISTS `zd_dept` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL DEFAULT '' COMMENT '部门编号',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '部门名称',
  `address` varchar(45) NOT NULL DEFAULT '' COMMENT '部门所在地址',
  `manager` int(10) unsigned NOT NULL COMMENT '部门经理',
  `phone` varchar(15) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  KEY `fk_zd_dept_zd_user1_idx` (`manager`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='部门表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `zd_doc`
--

CREATE TABLE IF NOT EXISTS `zd_doc` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '文档标题',
  `keywords` varchar(45) NOT NULL DEFAULT '' COMMENT '关键词',
  `content` text NOT NULL COMMENT '内容',
  `project` int(10) unsigned NOT NULL COMMENT '所属项目',
  `addedBy` int(10) unsigned NOT NULL COMMENT '添加者',
  `editedBy` int(10) unsigned NOT NULL COMMENT '最后编辑人',
  `addedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '添加时间',
  `editedTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `fk_zd_doc_zd_project1_idx` (`project`),
  KEY `fk_zd_doc_zd_user1_idx` (`addedBy`),
  KEY `fk_zd_doc_zd_user2_idx` (`editedBy`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='项目文档表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `zd_project`
--

CREATE TABLE IF NOT EXISTS `zd_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '项目名称',
  `code` varchar(15) NOT NULL DEFAULT '' COMMENT '项目编号',
  `begin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `end` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '项目截止时间',
  `desc` varchar(255) NOT NULL DEFAULT '' COMMENT '项目描述',
  `demand` text NOT NULL COMMENT '项目需求',
  `creator` int(10) unsigned NOT NULL,
  `finished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否完成',
  `progress` tinyint(4) NOT NULL DEFAULT '0' COMMENT '项目进度',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='项目表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `zd_task`
--

CREATE TABLE IF NOT EXISTS `zd_task` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '任务标题',
  `type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '任务类型，1设计，2开发，3测试，4研究，5讨论，6界面，7事务，8其他',
  `desc` text NOT NULL COMMENT '任务描述',
  `finished` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否完成',
  `project` int(10) unsigned NOT NULL COMMENT '所属项目',
  `creator` int(10) unsigned NOT NULL COMMENT '任务发起人',
  `solver` int(10) unsigned NOT NULL COMMENT '任务解决者',
  `assigner` int(10) unsigned NOT NULL DEFAULT '0',
  `assignedTo` int(10) unsigned NOT NULL COMMENT '被分配任务的人',
  `remark` text NOT NULL,
  `createTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `finishTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '完成时间',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否删除',
  PRIMARY KEY (`id`),
  KEY `fk_zd_task_zd_project1_idx` (`project`),
  KEY `fk_zd_task_zd_user1_idx` (`creator`),
  KEY `fk_zd_task_zd_user2_idx` (`assignedTo`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='任务表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `zd_team`
--

CREATE TABLE IF NOT EXISTS `zd_team` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(15) NOT NULL DEFAULT '' COMMENT '团队编号',
  `name` varchar(30) NOT NULL DEFAULT '' COMMENT '团队名称',
  `createTime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `project` int(10) unsigned NOT NULL COMMENT '负责项目',
  `leader` int(10) unsigned NOT NULL COMMENT '队长',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_UNIQUE` (`code`),
  KEY `fk_zd_team_zd_project1_idx` (`project`),
  KEY `fk_zd_team_zd_user1_idx` (`leader`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='团队表' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `zd_user`
--

CREATE TABLE IF NOT EXISTS `zd_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `account` varchar(30) NOT NULL DEFAULT '' COMMENT '账号名',
  `password` char(32) NOT NULL DEFAULT '' COMMENT '密码',
  `avatar` varchar(60) NOT NULL DEFAULT '' COMMENT '用户头像',
  `realname` varchar(30) NOT NULL DEFAULT '' COMMENT '真实名称',
  `sex` tinyint(4) NOT NULL DEFAULT '0' COMMENT '性别，默认为0，男',
  `phone` varchar(15) NOT NULL DEFAULT '' COMMENT '联系电话',
  `email` varchar(45) NOT NULL DEFAULT '' COMMENT '邮箱',
  `dept` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属部门',
  `team` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属团队',
  `verified` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否通过管理员验证',
  `ip` varchar(15) NOT NULL DEFAULT '' COMMENT '上次登录ip',
  `last` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上次登陆时间',
  `permission` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '用户权限，默认为0，无任何权限，为1是普通程序员，为2是团队队长，3是项目经理，4是系统管理员',
  `deleted` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '是否被删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_UNIQUE` (`account`),
  KEY `fk_zd_user_zd_dept_idx` (`dept`),
  KEY `fk_zd_user_zd_team1_idx` (`team`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='前台用户表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
