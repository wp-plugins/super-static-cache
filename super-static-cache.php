<?php
/*
Plugin Name: Super Static Cache
Plugin URI: http://www.hitoy.org/super-static-cache-for-wordperss.html
Description: Super static Cache plugins for Wordpress with a simple configuration and more efficient caching Efficiency, to make your website loader faster than ever. It will cache the html content of your post directly into your website directory. 
Version: 3.0.7
Author: Hitoy
Author URI: http://www.hitoy.org/
 */

//获取当前页面类型
function getpagetype(){
    if(is_trackback()){
        //文章的trackback也属于single, 所以is_trackback要放在前面
        return 'trackback';
    }else if(is_feed()){
        return 'feed';
    }else if(is_admin()){
        return 'admin';
    }else if(is_preview()){
        return 'preview';
    }else if(is_404()){
        return '404';
    }else if(is_search()){
        return 'search';
    }else if(is_single()){
        return 'single';
    }else if(is_tag()){
        return 'tag';
    }else if(is_category()){
        return 'category';
    }else if(is_page()){
        return 'page';
    }else if(is_home()){
        return 'home';
    }else if(is_archive()){
        return 'archive';
    }
    return 'notfound';
}

//递归删除文件
function delete_uri($uri){
    if(!file_exists($uri)) return '';
    if(is_file($uri)){return unlink($uri);}
    $fh = opendir($uri);  
    while(($row = readdir($fh)) !== false){  
        if($row == '.' || $row == '..' || $row == 'rewrite_ok.txt'){  
            continue;  
        }  
        if(!is_dir($uri.'/'.$row)){  
            unlink($uri.'/'.$row);  
        }  
        delete_uri($uri.'/'.$row);  
    }  
    closedir($fh);  
    //删除文件之后再删除自身  
    @rmdir($uri); 
}

//获取远程url的函数
//用来更新缓存
function build_cache($url){
    if(function_exists("curl_init")){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_REFERER,$url);
        curl_setopt($ch, CURLOPT_TIMEOUT,10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT,'SSCS/3 (Super Static Cache Spider/3.0; +http://www.hitoy.org/super-static-cache-for-wordperss.html#Spider)');
        curl_exec($ch); 
        curl_close($ch); 
    }else{
        file_get_contents($url);
    }
}


//根据post_id获取所有与文章相关的页面
//用来在文章更新时，更新这些页面
function get_related_page($post_id){
    $urls=array();
    //category
    $cates = get_the_category($post_id);
    foreach($cates as $c){
        array_push($urls,get_category_link($c->term_id));
    }
    //tag
    $tags = get_the_tags($post_id);
    if($tags){
        foreach($tags as $t){
            array_push($urls,get_tag_link($t->term_id));
        }
    }
    return $urls;
}


//缓存类
class WPStaticCache{
    public $wppath;            //WP安装路径，服务器的绝对路径
    public $docroot;           //网站的DOCUMENt_ROOT
    public $cachemod;          //缓存方式，关闭，直接缓存，服务器重写，PHP重写
    private $wpuri;             //用户访问的页面在服务器上存放的地址,相对于wp安装目录
    private $cachetag;
    private $htmlcontent;
    //不缓存的页面，默认
    private $nocachepage = array('admin','404','search','preview','trackback');

    //是否是严格模式缓存，默认开启
    //开启严格模式将不缓存既没有后缀，又没有以"/"结尾的uri
    private $isstrict;          

    //siteurl
    public $siteurl;

    public function __construct(){
        $this->docroot = str_replace("//","/",str_replace("\\","/",$_SERVER["DOCUMENT_ROOT"])."//");
        $this->wppath = str_replace("\\","/",ABSPATH);
        $this->cachemod = get_option("super_static_cache_mode");
        $this->cachetag="\n<!-- This is the static html file created at ".current_time("Y-m-d H:i:s")." by super static cache -->";
        $this->isstrict = (bool) get_option('super_static_cache_strict');

        //获取用户指定的不缓存的页面,并和系统自定义的合并到一块
        $usetnocache=trim(get_option("super_static_cache_excet"));
        $usernocachearr=empty($usetnocache)?array():explode(',',$usetnocache);
        $usernocachearr=array_map('trim',$usernocachearr);
        $this->nocachepage=array_merge($this->nocachepage,$usernocachearr);

        //获取wpuri,相对与WP安装目录
        $fullrequesturi=$this->docroot.urldecode($_SERVER["REQUEST_URI"]);
        $this->wpuri=str_replace("//","/",$fullrequesturi);
        $this->wpuri=substr($fullrequesturi,strlen($this->wppath));

        $this->siteurl=get_option('siteurl');
    }


    /*获取当前配置是否支持当前缓存模式
     * 不支持缓存的情况:
     * 1,缓存功能没有开启
     * 2,固定链接没有设置
     * 3,缓存模式为重写，但是重写规则没有更新
     * 4,开启严格缓存模式，且固定链接不以"/"且没有有后缀的文件名结束
     * 5,设置的为常规模式, 但是固定连接中含有目录设置, 可能导致某些页面出现访问文件(返回403或者目录文件列表)
     */
    public function is_permalink_support_cache(){
        $permalink_structure=get_option("permalink_structure");
        //对固定链接进行分析
        //反斜杠出现的的次数
        $dircount=substr_count($permalink_structure,'/');
        //去掉目录之后的文件名
        $fname=substr($permalink_structure,strripos($permalink_structure,"/")+1);

        if($this->cachemod == 'close'){
            return array(false,__('Cache feature is turned off'));
        }else if(empty($permalink_structure)){
            return array(false,__('You Must update Permalink to enable Super Static Cache','super_static_cache'));
        }else if($this->cachemod == 'serverrewrite' && !@fopen($this->siteurl."/rewrite_ok.txt","r")){
            return array(false,__('Rewrite Rules Not Update!','super_static_cache'));
        }else if($this->isstrict && $fname != "" && !strstr($fname,".")){
            return array(false,__('Strict Cache Mode not Support current Permalink!','super_static_cache'));
        }else if($this->cachemod == 'direct' && $dircount > 2){
            return array(false,__('Cache is enabled, But Some Pages May return 403 status or a index page cause your Permalink Settings','super_static_cache'));
        }
        return array(true,__('OK','super_static_cache'));
    }

    //获取当前页面类型是否支持缓存
    private function is_pagetype_support_cache(){
        if (in_array(getpagetype(),$this->nocachepage)){
            return false;
        }
        //登陆用户不缓存
        if(is_user_logged_in()){
            return false;
        }
        return true;
    }


    //主函数，开始进行缓存，注册到template_redirect上
    //只支持GET和POST两种请求方式
    public function init(){
        if($this->cachemod == 'phprewrite' && file_exists($this->get_cache_fname())){
            //PHP缓存模式时，这里进行匹配并获取缓存内容
            echo file_get_contents($this->get_cache_fname());
            exit();
        }
        //只对GET请求作出缓存
        if($_SERVER['REQUEST_METHOD'] == "GET"){
            ob_start(array($this,"get_request_html"));
            register_shutdown_function(array($this,"save_cache_content"));
        }
    }

    //获取当前访问页面的HTML内容
    public function get_request_html($html){
        $this->htmlcontent=trim($html).$this->cachetag;
        return trim($html);
    }

    //获取要缓存到硬盘上的缓存文件文件名
    //1, 如果缓存模式关闭，也直接返回空
    //2, 当前页面类型如果不支持缓存，那么直接返回空
    //3, 当uri含有.或者以/结尾时，都可缓存 (http://www.example.com/a.html或http://www.example.com/a/,排除的情况http://www.example.com/a)
    //4, 缓存模式为phprewrite或者serverrewrite时，缓存3以外的情况
    //5, 非严格模式，缓存模式为direct时，缓存3以外的情况
    //6, 其它均不给与缓存
    public function get_cache_fname(){
        //1,
        if($this->cachemod == 'close') return false;

        //2,
        if(!$this->is_pagetype_support_cache()) return false;

        preg_match("/^([^?]+)?/i",$this->wpuri,$match);
        $realname=urldecode($match[1]);
        //去掉目录之后的文件名
        $fname=substr($realname,strripos($realname,"/")+1);

        if($this->cachemod == 'serverrewrite' || $this->cachemod == 'phprewrite'){
            $cachedir='super-static-cache';
        }else {
            $cachedir='';
        }

        if($fname == ""){
            //以'/'结尾的请求
            $cachename = $this->wppath.$cachedir.$realname."index.html";
        }else if(strstr($fname,".")){
            //含有后置的请求
            $cachename = $this->wppath.$cachedir.$realname;
        }else if($this->cachemod != 'direct'){ 
            //不管是否严格模式，只要缓存模式不为direct时，都给于缓存
            $cachename = $this->wppath.$cachedir.$realname."/index.html";
        }else if(!$this->isstrict && $this->cachemod == 'direct'){
            //非严格模式，但是缓存模式为direct时,给于缓存
            $cachename = $this->wppath.$cachedir.$realname."/index.html";
        }else {
            $cachename = false;
        }
        return $cachename;
    }

    //写入并保存缓存
    public function save_cache_content(){
        $filename = $this->get_cache_fname();
        if($filename && strlen($this->htmlcontent) > 0){
            if(!file_exists(dirname($filename))){
                @mkdir(dirname($filename),0777,true);
            }
            file_put_contents($filename,$this->htmlcontent,LOCK_EX);
        }
    }

    //删除缓存
    //传入的参数页面的绝对地址
    //如http://localhost/hello-wrold/
    public function delete_cache($url){
        $uri=substr($url,strlen($this->siteurl));
        if($this->cachemod == 'serverrewrite' || $this->cachemod == 'phprewrite'){
            $uri=$this->wppath.'super-static-cache'.$uri;
        }else if($this->cachemod == 'direct'){
            $uri=$this->wppath.$uri;
        }
        delete_uri($uri);
        if(file_exists($uri)){
            return false;
        }else{
            return true;
        }
    }

    //文章更新时的动作
    //重新建立缓存
    public function post_update($id,$post){
        //更新首页
        $this->delete_cache($this->siteurl.'/index.html');
        build_cache($this->siteurl);

        //更新文章页
        $url=get_permalink($id);
        $this->delete_cache($url);
        build_cache($url);

        //更新和文章页有关联的其它页面
        $list=get_related_page($id);
        foreach($list as $u){
            $this->delete_cache($u);
            build_cache($u);
        }
    }

    //文章删除时的动作
    //删除文章，重建缓存
    public function trash_post($id){
        //更新首页
        $this->delete_cache($this->siteurl.'/index.html');
        build_cache($this->siteurl);

        //删除文章页
        $url=get_permalink($id);
        $this->delete_cache($url);

        //更新和文章页有关联的其它页面
        $list=get_related_page($id);
        foreach($list as $u){
            $this->delete_cache($u);
            build_cache($u);
        }
    }


    //安装函数
    public function install(){
        add_option("super_static_cache_mode","close");
        add_option("super_static_cache_excet","author,feed");
        add_option("super_static_cache_strict",false);

        //创建rewrite缓存目录
        if(!file_exists($this->wppath.'super-static-cache')){
            @mkdir($this->wppath.'super-static-cache',0777,true);
        }
        file_put_contents($this->wppath."super-static-cache/rewrite_ok.txt","This is a test file from rewrite rules,please do not to remove it.\n");
    }
    //卸载函数
    public function unistall(){
        delete_option("super_static_cache_mode");
        delete_option("super_static_cache_excet");
        delete_option("super_static_cache_strict");
        //删除
        unlink($this->wppath."super-static-cache/rewrite_ok.txt");
        delete_uri($this->wppath.'super-static-cache');
    }

}

$wpssc = new WPStaticCache();
add_action("template_redirect",array($wpssc,"init"));


//后台界面展示
if(is_admin()){
    //安装和卸载
    register_activation_hook(__FILE__,array($wpssc,'install'));
    register_deactivation_hook(__FILE__,array($wpssc,'unistall'));

    //文章更新
    add_action('publish_post',array($wpssc,'post_update'),10,2);
    add_action('post_updated ',array($wpssc,'post_update'),10,2);

    //删除文章动作
    add_action("trashed_post",array($wpssc,'trash_post'));

    //后台管理界面
    require_once("super-static-cache-admin.php");

    //加载语言
    load_plugin_textdomain('super_static_cache', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
