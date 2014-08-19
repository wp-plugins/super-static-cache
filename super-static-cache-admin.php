<?php
if(isset($_POST["super_static_cache_mode"])){
	update_option("super_static_cache_mode",trim($_POST["super_static_cache_mode"]));
}else if(isset($_POST["super_static_cache_excet"])){
	$exceptpagelist=$_POST["super_static_cache_excet"];
	$except=implode(",",$exceptpagelist);
	update_option("super_static_cache_excet",$except);
}

function display_cache_menu(){
	add_options_page('Super Static Cache', 'Super Static Cache', 'manage_options','super-static-cache-admin.php', 'show_cache_manage');
}
function show_cache_manage(){
	$super_static_cache_mode=get_option("super_static_cache_mode");
	$super_static_cache_excet=get_option("super_static_cache_excet");
	$exceptpagelist=explode(",",$super_static_cache_excet);
	if(!is_writeable(get_home_path())){
		echo "警告:wordpress安装目录不能写，缓存插件将不生效!";
		$super_static_cache_mode="close";
	}else if (!_is_support_direct_cache()){
		echo "警高:您的固定连接格式不支持缓存，请更改固定连接格式!";
	}
?>
<div class="wrap">
<h2>Super Static Cache 插件设置</h2>
<hr/>
<h3>缓存模式</h3>
<p>直接缓存和PHP缓存的区别: 直接缓存会在web目录直接生成静态文档，下次访问Web服务器会直接访问此文档，这意味着您的网站将有更高的访问速度，但是这些文档将不再受到wordperss的控制(意味着您的访问控制插件将不再起作用)。此版本PHP缓存模式暂未开启<br/>
</p>
<form action="" method="POST">
<input type="radio" name="super_static_cache_mode" value="close" <?php echo $super_static_cache_mode=="close"?"checked=\"checked\"":"";?>/>关闭
&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="direct" <?php echo $super_static_cache_mode=="direct"?"checked=\"checked\"":"";?>/>直接缓存
&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="php" <?php	echo $super_static_cache_mode=="php"?"checked=\"checked\"":"";?> disabled="disabled"/>PHP缓存
<br/><br/><input class="button-primary" type="submit" value="Update »">
</form>
	<br/>
	<h3>例外页面</h3>
	<p>如果您勾选，下列页面将不予缓存。对已经缓存过的页面没有影响。</p>
	<form action="" method="POST">
	<input type="checkbox" name="super_static_cache_excet[]" value="home" <?php if(in_array("home",$exceptpagelist)){echo "checked=\"checked\"";}?>/>首页<br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="single" <?php if(in_array("single",$exceptpagelist)){echo "checked=\"checked\"";}?>/>内容页<br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="category" <?php if(in_array("category",$exceptpagelist)){echo "checked=\"checked\"";}?>/>分类页面<br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="page" <?php if(in_array("page",$exceptpagelist)){echo "checked=\"checked\"";}?>/>单页面<br/>
	<input type="checkbox" name="super_static_cache_excet[]" value="feed" <?php if(in_array("feed",$exceptpagelist)){echo "checked=\"checked\"";}?>/>Rss源<br/>
<br/>
	<input class="button-primary" type="submit" value="<?php _e('Update','wp-real-ip-based-access-control')?> »">
	</form>
</div>
<?php
}
add_action('admin_menu', 'display_cache_menu');
