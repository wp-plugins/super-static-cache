<?php
//设置页面

//更新语言设置
load_plugin_textdomain('super_static_cache', false, dirname(plugin_basename(__FILE__)) . '/languages');

//展示菜单
function display_cache_menu(){
	add_options_page('Super Static Cache', 'Super Static Cache', 'manage_options','super-static-cache-admin.php', 'show_cache_manage');
}

function show_cache_manage(){
	global $staticcache;
	$cacheable=$staticcache->is_support_cache();
	$warning=array();
	$super_static_cache_mode=$staticcache->get_option("super_static_cache_mode");

	$super_static_cache_excet=$staticcache->get_option("super_static_cache_excet");
	$exceptpagelist=explode(",",$super_static_cache_excet);
	if(!is_writeable(get_home_path())){
		array_push($warning, __("The plugin prohibited: You dont have write permission on ",'super_static_cache').get_home_path());
		$super_static_cache_mode="close";
	}
	if(!$cacheable){
		array_push($warning, __("Your Permalinks Setting are not support cache!",'super_static_cache'));
		$super_static_cache_mode="close";
	}
	if(PHP_VERSION < "5.0.0"){
		array_push($warning, __("Your PHP version too low to support cache!",'super_static_cache'));
		$super_static_cache_mode="close";
	}

	//更新配置和删除功能
	if(isset($_POST["super_static_cache_mode"])){
		//$staticcache->update_option("super_static_cache_mode",trim($_POST["super_static_cache_mode"]));
		update_option("super_static_cache_mode",trim($_POST["super_static_cache_mode"]));
	}else if(isset($_POST["super_static_cache_excet"])){
		$exceptpagelist=$_POST["super_static_cache_excet"];
		$except=implode(",",$exceptpagelist);
		update_option("super_static_cache_excet",$except);
	}else if(isset($_POST["purgesinglefile"])){
		$del_re=$staticcache->delete_cache($_POST["purgesinglefile"]);
		if($del_re){
			array_push($warning,__("Purge ",'Super Static Cache').$_POST["purgesinglefile"].__(" Success",'Super Static Cache'));
		}else{
			array_push($warning,__("Purge ",'Super Static Cache').$_POST["purgesinglefile"].__(" Failure",'Super Static Cache'));
		}
	}
?>
<div class="wrap">
<?php
	if(!empty($warning)){
		echo "<div style=\"width:96%;padding:2%;background:#B7D69F\">";
		echo "<strong style=\"font-size:18px\">".__("Notice","super_static_cache").":</strong><br/>";
		echo implode("<br/>",$warning);
		echo "</div>";
	}
?>
<h2>Super Static Cache</h2>
<hr/>
<div style="background:#ffc;border:1px solid #333;margin:2px;margin-top:10px;padding:5px;float:right;width:260px">
	<h3 style="text-align:center"><?php _e("About Super Static Cache","super_static_cache");?></h3>
	<p><?php _e('Super Static Cache is developing and maintaining by <a href="http://www.hitoy.org/" target="_blank">Hito</a>.<br/>It is a advanced fully static cache plugin, with easy configuration and high efficiency. When a post cached, It will no longer need the Database. It is a better choice when your posts more than 5000.','super_static_cache');?></p>
	<p><?php _e('Have any suggestions, please contact vip@hitoy.org.','super_static_cache');?></p>
	<h3 style="text-align:center"><?php _e("Rating for This Plugin","super_static_cache");?></h3>
	<p><?php _e('Please <a href="http://wordpress.org/support/view/plugin-reviews/super-static-cache" target="_blank">Rating for this plugin</a> and tell me your needs. This is very useful for my development.','super_static_cache');?></p>
	<h3 style="text-align:center"><?php _e("Help Me","super_static_cache");?></h3>
	<p><?php _e("You can Donate to this plugin to let this plugin further improve. You Can also help me to <a href='mailto:vip@hitoy.org'>Improve translation</a>.","super_static_cache");?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3EL4H6L7LY3YS">
<input type="image" src="http://www.hitoy.org/wp-content/uploads/donate_paypal.gif" border="0" name="submit" alt="PayPal">
<img alt="" border="0" src="https://www.paypalobjects.com/zh_XC/i/scr/pixel.gif" width="1" height="1">
</form>
</div>

<h3><?php _e("Caching Mode","super_static_cache");?></h3>

	<p><?php _e("Different between direct and rewrite caching: direct caching will caching all the file on your WP install path, rewrite caching will create a directory on your WP install path, all the caching files will storage in this directory, you need to update a rewrite rule to enable this function.","super_static_cache");?><br/></p>

<form action="" method="POST">
<input type="radio" name="super_static_cache_mode" value="close" <?php echo $super_static_cache_mode=="close"?"checked=\"checked\"":"";?>/><?php _e("close","super_static_cache");?>
&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="direct" <?php echo $super_static_cache_mode=="direct"?"checked=\"checked\"":"";?>/><?php _e("direct caching (Recommended)","super_static_cache");?>
&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="rewrite" <?php echo $super_static_cache_mode=="rewrite"?"checked=\"checked\"":"";?> onclick="showrewriterules();"/><?php _e("rewrite caching","super_static_cache");?>
<br/><br/>
<?php
	$software=strtolower($_SERVER["SERVER_SOFTWARE"]);
	$docuroot=$staticcache->docuroot;
	$homepath=get_home_path();
	$rewriterules="";
	if($docuroot==$homepath){
		switch ($software){
		case strstr($software,"nginx"):
			$rewriterules='try_files $uri $uri/index.html /super-static-cache$uri /super-static-cache$uri/index.html $uri/ /index.php';
			break;
		case strstr($software,"apache"):
			$rewriterules=htmlspecialchars(file_get_contents(dirname(__FILE__)."/apache_rewrite_root"));
			break;
		case strstr($software,"iis"):
			$rewriterules=__("Sorry, Not support IIS yet","super_static_cache");
			break;
		default:
			$rewriterules=__("Sorry, Your web server is not detected","super_static_cache");

		}
	}else if($docuroot!=$homepath){
		$webdir=substr($homepath,strlen($docuroot)-1);
		switch ($software){
		case strstr($software,"nginx"):
			$rewriterules="location ~* $webdir(.*)$ {\n\ttry_files \$uri \$uri/index.html $webdir"."super-static-cache/$1 ".$webdir."super-static-cache/$1/index.html \$uri/".$webdir."index.php; \n}";
			break;
		case strstr($software,"apache"):
			$rewriterules=file_get_contents(dirname(__FILE__)."/apache_rewrite_dir");
			$rewriterules=str_replace("wp_install_dir",trim($webdir,"/"),$rewriterules);
			$rewriterules=htmlspecialchars($rewriterules);
			break;
		case strstr($software,"iis"):
			$rewriterules=__("Sorry, Not support IIS yet","super_static_cache");
			break;
		default:
			$rewriterules=__("Sorry, Your web server is not detected","super_static_cache");
		}


	}
?>
	<script>
	function showrewriterules(){
<?php 
	$siteurl=get_option("siteurl");
	if(!@fopen($siteurl."/rewrite_ok.txt","r")){
		echo "document.getElementById('rewriterules_show_func').style.display='block'";
	}
?>
	}
	</script>
<div style="display:none" id="rewriterules_show_func">
<h3 style="font-size:12px">Please Add This rewrite Rules into your Web Server:</h3>
<pre style="background:white;padding:5px;margin:5px;overflow:auto">
<?php echo $rewriterules; ?>
</pre>
</div>
<input class="button-primary" type="submit" value="<?php _e('update',"super_static_cache");?> »">
</form>
	<br/>
	<h3><?php _e("No Caching Page","super_static_cache");?></h3>
	<p><?php _e("If you select, the plugin will not caching these pages.Cached files will not affected. If you select one, you need to delete the cached file.","super_static_cache");?></p>
	<form action="" method="POST">
	<input type="checkbox" name="super_static_cache_excet[]" value="home" <?php if(in_array("home",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Home Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="single" <?php if(in_array("single",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Post Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="category" <?php if(in_array("category",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Category Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="page" <?php if(in_array("tag",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Tag Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="author" <?php if(in_array("author",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Author Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="author" <?php if(in_array("date",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("date Page","super_static_cache");?><br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="feed" <?php if(in_array("feed",$exceptpagelist)){echo "checked=\"checked\"";}?>/><?php _e("Feed(Recommended)","super_static_cache")?><br/>
<br/>
	<input class="button-primary" type="submit" value="<?php _e('update',"super_static_cache")?> »">
	</form>
<br/>
<h3><?php _e("Purge cache files","super_static_cache");?></h3>
<p><?php _e("You Can Input a cache file name to delete it. If you enter a directory name, All the files in this directory and directory itself will be deleted.<br/>If you enter a filename, It just delete the file. Plugin Just delete files in WP install Page. <br/>For example, you input /a.html, It will purge a.html on your wordpress path rather than the other documents","super_static_cache");?></p>
<form action="" method="POST" onsubmit="return confirm('<?php _e("Are you really want to do this?","super_static_cache");?>')">
<input type="text" name="purgesinglefile" style="width:400px"><br/><br/>
<input type="submit" class="button-primary" value="<?php _e("Purge Files","super_static_cache");?>"/>
</form>
</div>
<?php
}
add_action('admin_menu', 'display_cache_menu');
