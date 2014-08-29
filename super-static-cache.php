<?php
/*
Plugin Name: Super Static Cache
Plugin URI: http://www.hitoy.org/super-static-cache-for-wordperss.html
Description: Super static Cache plugins for Wordpress with a simple configuration and more efficient caching Efficiency, to make your website loader faster than ever. It will cache the html content of your post directly into your website directory. 
Version: 2.0.1
Author: Hitoy
Author URI: http://www.hitoy.org/
 */
class WpstaticCache{
	private $wppath;		//wordpress安装路径
	private $wpuri;			//请求资源名,相对于wordpress，wordpress有可能在二级目录安装，这里必须要获取好
	private $cachemod;		//缓存方式
	private $nocachepage;	//不需要缓存的页面
	private $staticdir;		//php模式时，缓存存放的路径
	private $cachetag;		//加到缓存后面的标签
	private $htmlcontent;	//文章的html内容
	public $docuroot;		//DOCUMENT_ROOT所在路径
	//初始化获取一些信息
	public function __construct(){
		$this->docuroot = str_replace("//","/",str_replace("\\","/",$_SERVER["DOCUMENT_ROOT"])."//");
		$this->wppath=str_replace("\\","/",ABSPATH);
		$this->cachemod=get_option("super_static_cache_mode");
		$this->nocachepage=get_option("super_static_cache_excet");
		$this->cachedir="super-static-cache";
		$this->cachetag="\n<!-- This is the static html file created at ".date("Y-m-d H:i:s")." by super static cache -->";

		//获取相对于当前wordpress安装路径的请求url
		$fullrequesturi=$this->docuroot.$_SERVER["REQUEST_URI"];
		$fullrequesturi=str_replace("//","/",$fullrequesturi);
		$this->wpuri=substr($fullrequesturi,strlen($this->wppath)-1);
	}

	//主函数，开始进行缓存，注册到template_redirect上
	//只有前台才触发
	public function init(){
		ob_start(array($this,"get_request_html"));
		register_shutdown_function(array($this,"save_cache_content"));
	}

	//获取当前访问页面的HTML内容
	public function get_request_html($html){
		$this->htmlcontent=trim($html).$this->cachetag;
		return trim($html);
	}

	//查看当前固定链接是否支持缓存
	//固定链接设置方式不能含有问号
	//固定链接结尾必须{有后缀或者结尾包涵"/"}
	public function is_support_cache(){
		$permalink_structure=get_option("permalink_structure");
		if(empty($permalink_structure)){
			return false;
		}else if(stripos($permalink_structure,"?")){
			return false;
		}else if( (substr($permalink_structure,strlen($permalink_structure)-1)!="/") && !strstr(substr($permalink_structure,strrpos($permalink_structure,"/")+1),".")){
			return false;
		}
		return true;
	}

	//获取请求的文件名,绝对路径
	//当缓存模式为直接缓存时，如果请求为目录，则加上index.html,如果还有请求数据，则为?之前的内容
	//当缓存模式为PHP模式时，文件名为最后一个/或问号之后的内容加上cacahe dir
	private function get_request_filename($uri){
		preg_match("/^([^?]+)?/i",$uri,$match);
		$realname=$match[1];
		if($this->cachemod=="direct"){
			if(substr($realname,strlen($realname)-1,1)=="/"){
				return $this->wppath.$realname."index.html";
			}else if(strrpos($realname,"/")<strrpos($realname,".")){
				return $this->wppath.$realname;
			}
		}else if($this->cachemod=="rewrite"){
			if(substr($realname,strlen($realname)-1,1)=="/"){
				return $this->wppath.$this->cachedir.$realname."index.html";
			}else if(strrpos($realname,"/")<strrpos($realname,".")){
				return $this->wppath.$this->cachedir.$realname;
			}
		}
		/*
		if($this->cachemod=="direct"&&$this->is_support_cache()){
			if(substr($realname,strlen($realname)-1,1)=="/"){
				return $this->wppath.$realname."index.html";
			}else if(strrpos($realname,"/")<strrpos($realname,".")){
				return $this->wppath.$realname;
			}else{
				return $this->wppath.$realname."/index.html";
			}
		}
		 */
		return false;//当前页面不支持缓存的情况返回空
	}


	//获取当前页面是否支持缓存
	//不支持缓存的几个条件:所有属于wordpress程序的页面
	//后台管理页面，404页面，预览页面，搜索页面,admin_bar展示的页面
	private function is_current_page_support_cache(){
		//404,后台页面，搜索页面，预览页面，adminbar展示页面不予缓存
		if(is_404()||is_search()||is_preview()||is_admin_bar_showing()){
			return false;
		}
		//
		//根据用户选择的页面不进行缓存
		$nocache=explode(",",$this->nocachepage);
		foreach($nocache as $singlepage){
			$sp="is_".$singlepage;
			if($sp()){
				return false;
			}
		}
		return true;
	}

	//开始缓存，把需要缓存的页面存入目录
	public function save_cache_content(){
		if($this->is_current_page_support_cache()){
			$filename=$this->get_request_filename($this->wpuri);
			if($filename){
				@mkdir(dirname($filename),0777,true);
				file_put_contents($filename,$this->htmlcontent,LOCK_EX);
			}
		}
	}

	//当文章更新时，需要更新以下页面
	//1.生成当前页面
	//2.更新列表页
	public function post_update($id,$post){
		$url=get_permalink($id);

		//更新首页
		@rename($this->wppath."index.html",$this->wppath."index.bak");
		@rename($this->wppath.$this->cachedir."./index.html",$this->wppath.$this->cachedir."/index.bak");

		//删除原来的缓存
		preg_match("/^[^:]+:\/\/[^\/]+(\S+)/i",$url,$match);
		$uri=substr(str_replace("//","/",$this->docuroot.$match[1]),strlen($this->wppath)-1);
		@unlink($this->get_request_filename($uri));

		//更新文章页
		if(function_exists("curl_init")){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url); 
			curl_setopt($ch, CURLOPT_REFERER,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_exec($ch); 
			curl_close($ch); 
		}else{
			file_get_contents($url);
		}
	}

	//当文章删除时，需要删除缓存
	//参数uri是相对于wordpress安装目录的uri
	public function delete_cache($uri){
		if(empty($uri)) return false;

		if($this->cachemod=="direct"){
			$aburi=$this->wppath.$uri;
		}else if($this->cachemod=="rewrite"){
			$aburi=$this->wppath.$this->cachedir.$uri;
		}
		if(!file_exists($aburi)){
			return false;
		}
		if(is_file($aburi)){
			return @unlink($aburi);
		}else if(is_dir($aburi)){
			//删除目录下所有文件
			$dir=opendir($aburi);
			while(($file = readdir($dir)) !== false){
				if($file=="."||$file=="..") continue;
				@unlink($aburi."/".$file);
			}
			closedir($dir);
			//删除目录
			return @rmdir($aburi);
		}
	}
	//删除文章时，WP的hook
	public function trash_post($id){
		$url=get_permalink($id);
		preg_match("/^[^:]+:\/\/[^\/]+(\S+)/i",$url,$match);
			$uri=substr(str_replace("//","/",$this->docuroot.$match[1]),strlen($this->wppath)-1);
		#@unlink($this->get_request_filename($uri));
		$this->delete_cache($uri);
	}

	//安装函数
	public function install(){
		add_option("super_static_cache_mode","close");
		add_option("super_static_cache_excet","author,feed");
		//创建rewrite缓存目录
		@mkdir($this->wppath.$this->cachedir,0777,true);
		file_put_contents($this->wppath.$this->cachedir."/rewrite_ok.txt","This is a test file from rewrite rules,please do not to remove it.\n");
	}
	//卸载函数
	public function unistall(){
		delete_option("super_static_cache_mode");
		delete_option("super_static_cache_excet");
	}

	//获取配置
	public function get_option($key){
		if($key=='super_static_cache_mode'){
			return $this->cachemod;
		}else if($key="super_static_cache_excet"){
			return $this->nocachepage;
		}
		return;
	}
	//更新配置
	public function update_option($key,$value){
		if($key=='super_static_cache_mode'){
			$this->cachemod=$value;
			update_option($key,$value);
		}else if($key=='super_static_cache_excet'){
			$this->nocachepage=$value;
			update_option($key,$value);
		}
	}
}

//开始运行
$staticcache=new WpstaticCache();

//前台启动
add_action("template_redirect",array($staticcache,"init"));

//文章更新
add_action('publish_post',array($staticcache,'post_update'),10,2);
add_action('post_updated ',array($staticcache,'post_update'),10,2);

//后台界面展示
if(is_admin()){
	//安装和卸载
	register_activation_hook(__FILE__,array($staticcache,'install'));
	register_deactivation_hook(__FILE__,array($staticcache,'unistall'));
	//删除文章动作
	add_action("trashed_post",array($staticcache,'trash_post'));
	//后台管理界面
	require("super-static-cache-admin.php");
}

