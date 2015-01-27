<?php
/*后台管理界面*/
/*最后更新 2015年1月26日*/

//更新配置
if($_POST['super_static_cache_mode']){
    $super_static_cache_mode=trim($_POST['super_static_cache_mode']);
    update_option('super_static_cache_mode',$super_static_cache_mode);
}
if($_POST['super_static_cache_excet']){
    $super_static_cache_excet_arr=$_POST['super_static_cache_excet'];
    $super_static_cache_excet = implode($super_static_cache_excet_arr,',');
    update_option('super_static_cache_excet',$super_static_cache_excet);
}
if($_POST['super_static_cache_strict']){
    $super_static_cache_strict=($_POST['super_static_cache_strict'] == "true")?true:false;
    update_option('super_static_cache_strict',$super_static_cache_strict);
}
if($_POST['purgesinglefile']){
    $delurl=trim($_POST['purgesinglefile']);
    $delurl=ltrim($delurl,'/');
    if($delurl == '') return;
    if(strpos($delurl,'.php')) return;
    if(strpos($delurl,'wp-admin') == 0) return;
    if(strpos($delurl,'wp-content') == 0) return;
    if(strpos($delurl,'wp-includes') == 0) return;
    $wpssc->delete_cache($wpssc->siteurl.'/'.$delurl);
}

//展示菜单
function display_cache_menu(){
    add_options_page('Super Static Cache', 'Super Static Cache', 'manage_options','super-static-cache-admin.php', 'show_cache_manage');
}
function show_cache_manage(){
    require_once(dirname(__FILE__).'/options.php');
}

add_action('admin_menu', 'display_cache_menu');
