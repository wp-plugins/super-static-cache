<?php
/*后台管理界面*/
/*最后更新 2015年1月26日*/

//展示菜单
function display_cache_menu(){
    add_options_page('Super Static Cache', 'Super Static Cache', 'manage_options',__FILE__, 'show_cache_manage');
}
function show_cache_manage(){
    require_once(dirname(__FILE__).'/options.php');
}
add_action('admin_menu', 'display_cache_menu');

//增加管理链接
function ssc_action_links($links,$pluginfile){
    if($pluginfile == 'super-static-cache/super-static-cache.php'){
        $link=array(
            '<a href="'. get_admin_url(null, 'options-general.php?page=super-static-cache/super-static-cache-admin.php') .'">'.__('Settings','super_static_cache').'</a>'
        );
        $links = array_merge($link, $links);
    }
    return $links;
}
add_filter('plugin_action_links', 'ssc_action_links',10,2);

//增加其它配置连接
function ssc_row_meta($links,$pluginfile){
    if($pluginfile == 'super-static-cache/super-static-cache.php'){
        $link=array(
            '<a href="'. get_admin_url(null, 'options-general.php?page=super-static-cache/super-static-cache-admin.php') .'">'.__('Settings','super_static_cache').'</a>',
            '<a href="http://www.hitoy.org/super-static-cache-for-wordperss.html">'.__('Support','super_static_cache').'</a>',
            '<a href="http://www.hitoy.org/super-static-cache-for-wordperss.html#Donations">'.__('Donate','super_static_cache').'</a>'
        );
        $links = array_merge($links,$link);
    }
    return $links;
}
add_filter('plugin_row_meta','ssc_row_meta',10,2);

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
    if(strripos($delurl,'.php')) return;
    if(strripos($delurl,'wp-admin')) return;
    if(strripos($delurl,'wp-content')) return;
    if(strripos($delurl,'wp-includes')) return;
    $wpssc->delete_cache($wpssc->siteurl.'/'.$delurl);
}
