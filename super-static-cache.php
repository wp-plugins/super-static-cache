<?php
/*
Plugin Name: Super Static Cache
Plugin URI: http://www.hitoy.org/super-static-cache-for-wordperss.html
Description: Super static Cache plugins for Wordpress with a simple configuration and more efficient caching Efficiency, to make your website loader faster than ever. It will cache the html content of your post directly into your website directory. 
Version: 1.0.1
Author: Hitoy
Author URI: http://www.hitoy.org/
 */

require_once(ABSPATH . 'wp-admin/includes/file.php');
//获取相当于document root的请求路径
define("requst_uri",$_SERVER["REQUEST_URI"]);
define("document_root",$_SERVER["DOCUMENT_ROOT"]);
//如果是以PHP方式读取文件的话,设置存储的路径,相当于wordpress安装路径
define("super_static_cache_dir","super_static_cache");
//缓存识别标签
define("cachetag","\n<!-- This is the static html file created at ".date("Y-m-d H:i:s")." by super static cache -->");

//安装时运行的函数，增加缓存方式的选项:直接缓存或者通过PHP方式
function _super_static_cache_install(){
	//缓存方式: 关闭close，直接direct, PHP重写php
	add_option("super_static_cache_mode","close","yes");
	//不缓存哪些页面
	add_option("super_static_cache_excet","home,author,feed","yes");
}

//删除时执行的函数，删除数据库缓存
function _super_static_cache_unstall(){
	delete_option("super_static_cache_mode");
	delete_option("super_static_cache_excet");
}
//获取缓存方式
function _get_static_cache_mode(){
	return get_option("super_static_cache_mode")?get_option("super_static_cache_mode"):"direct";
}

//更新缓存方式
function _update_static_cache_mode($str){
	if(update_option("super_static_cache_mode",$str,"yes")){
		return true;
	}
	return false;
}

//获取是否支持直接缓存
function _is_support_direct_cache(){
	//获取固定链接方式
	$permalink_structure=get_option("permalink_structure");
	if(empty($permalink_structure)){
		return false;
	}
	//如果固定链接最后含有"/"或者文件后缀，则支持直接缓存，反之不支持
	if(strrpos($permalink_structure,"/")==strlen($permalink_structure)-1){
		return true;
	}else if(strpos(substr($permalink_structure,strripos($permalink_structure,"/")),".")){
		return true;
	}
	return false;
}

//获取缓存到服务器上的文件名,服务器绝对路径
function _get_static_cache_name(){
	//支持并设置为直接缓存的情况
	if(_get_static_cache_mode()=="direct"){
		//如果访问方式为目录 类似 "/postname/"，则自动加上index.html
		//如果含有后缀，则使用原始值
		if(strrchr(requst_uri,"/")=="/"){
			return document_root.requst_uri."index.html";
		}
		return document_root.requst_uri;
	}else{
		//获取缓存位置
		return document_root.super_static_cache_dir.requst_uri;
	}
}
//创建并保存文件函数
function create_file($filename,$content){
	preg_match("/^([^\?]+)\?*([^\/]*)/i",$filename,$match);
	$filename=$match[1];
	$dir=substr($filename,0,strrpos($filename,"/"));
	mkdirs($dir);
	$handle=fopen($filename,"w");
	do{
		usleep(80);
	}while(!flock($handle,LOCK_EX));
	fwrite($handle,$content);
	flock($handle,LOCK_UN);
	fclose($handle);
}
//递归创建目录
function mkdirs($dir){
	if(!is_dir($dir)){
		if(!mkdirs(dirname($dir))){
			return false; 
		}if(!mkdir($dir,0777)){
			return false; 
		} 
	} return true; 
} 

//获取需要缓存的html内容,执行主动作
function get_cache_content(){
	ob_start("save_cache_content");
}

//存储缓存内容
function save_cache_content($html){
	//没有开启缓存，则直接返回
	if(_get_static_cache_mode()=="close"){
		return $html;
	}
	//固定连接不满足缓存条件
	if(!_is_support_direct_cache()){
		return $html;
	}
	//不满足缓存条件，后台则直接返回
	if(is_admin()||is_404()||is_search()){
		return $html;
	}
	//不满足缓存条件，指定缓存页面之外
	$power=trim(trim(get_option("super_static_cache_excet"),","));
	$list=explode(",",$power);
	foreach($list as $s){
		$s="is_".$s;
		if($s())
			return $html;
	}
	
	//缓存
	$filename=_get_static_cache_name();
	$content=$html.cachetag;
	create_file($filename,$content);
	return $html;
}

//更新文章时的hook
function publist_hook($id){
	if($id=="") return true;
	$siteurl=get_option("siteurl");
	@rename(get_home_path()."index.html",get_home_path()."index.bak");
	@file_get_contents($siteurl."/p=".$id);
}
//这里开始缓存
add_action("pre_get_posts","get_cache_content");
//当更新文章时的动作
add_action('publish_post','publist_hook');
//安装动作
register_activation_hook(__FILE__,'_super_static_cache_install');
//停用插件运行
register_deactivation_hook(__FILE__,'_super_static_cache_unstall');

//后台管理页面
if(is_admin()){
require("super-static-cache-admin.php");
}
