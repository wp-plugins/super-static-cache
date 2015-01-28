<?php
global $wpssc;

function setselected($key,$value,$checkbox='checked=checked'){
    if($key == get_option($value) || strpos(get_option($value),$key) !== false)
        return $checkbox;
}
function is_rewrite_ok(){
global $wpssc;
if(@fopen($wpssc->siteurl."/rewrite_ok.txt","r")){
    return true;
}
return false;
}

function getwebserver(){
    $software=strtolower($_SERVER["SERVER_SOFTWARE"]);
    switch ($software){
    case strstr($software,"nginx"):
        return "nginx";
        break;
    case strstr($software,"apache"):
        return "apache";
        break;
    case strstr($software,"iis"):
        return "iis";
        break;
    default:
        return "unknown";
    }
}


function getwpinstallpath(){
    global $wpssc;
    return "/".substr($wpssc->wppath,strlen($wpssc->docroot));
}

function showrewriterule(){
    global $wpssc;
    $cachemod=$wpssc->cachemod;
    $is_rewrite_ok=is_rewrite_ok();
    $webscr=getwebserver();
    if ($cachemod == 'serverrewrite' && !$is_rewrite_ok && $webscr == 'apache'){
        $rwt=file_get_contents(dirname(__FILE__)."/apache_rewrite_rule");
        return str_replace('/wp_install_dir/',getwpinstallpath(),$rwt);
    }else if($cachemod == 'serverrewrite' && !$is_rewrite_ok && $webscr == 'nginx'){
        $rwt=file_get_contents(dirname(__FILE__)."/nginx_rewrite_rule");
        return str_replace('/wp_install_dir/',getwpinstallpath(),$rwt);
    }else if($cachemod == 'serverrewrite' && !$is_rewrite_ok){
        return (__('Your Webserver is ').$webscr.__('We Can not generation a Rewrite Rules for you!'));
    }
    return false;
}

?>
<div class="wrap">
<?php 
$notice=$wpssc->is_permalink_support_cache();
if(!$notice[0])
    echo '<div style="width:96%;padding:2%;background:#B7D69F"><strong style="font-size:18px">Notice:</strong><br>'.$notice[1].'</div>'
?>
<h2><?php _e("Super Static Cache Settings","super_static_cache");?></h2><hr/>
<div style="background:#ffc;border:1px solid #333;margin:2px;margin-top:10px;padding:5px;float:right;width:260px">
<h3 style="text-align:center"><?php _e("About Super Static Cache","super_static_cache");?></h3>

<?php
_e("<p>Super Static Cache is developing and maintaining by <a href=\"http://www.hitoy.org\/\" target=\"_blank\">Hito</a>.<br>It is a advanced fully static cache plugin, with easy configuration and high efficiency. When a post cached, It will no longer need the Database. It is a better choice when your posts more than 5000.</p>
<p>Have any suggestions, please contact vip@hitoy.org.</p>
<h3 style=\"text-align:center\">Rating for This Plugin</h3>
<p>Please <a href=\"http://wordpress.org/support/view/plugin-reviews/super-static-cache\" target=\"_blank\">Rating for this plugin</a> and tell me your needs. This is very useful for my development.</p>
<h3 style=\"text-align:center\">Help Me</h3>
<p>You can Donate to this plugin to let this plugin further improve. You Can also help me to <a href=\"mailto:vip@hitoy.org\">Improve translation</a>.</p>
<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" target=\"_blank\">
<input type=\"hidden\" name=\"cmd\" value=\"_s-xclick\">
<input type=\"hidden\" name=\"hosted_button_id\" value=\"3EL4H6L7LY3YS\">
<input type=\"image\" src=\"http://www.hitoy.org/wp-content/uploads/donate_paypal.gif\" border=\"0\" name=\"submit\" alt=\"PayPal\">
<img border=\"0\" src=\"https://www.paypalobjects.com/zh_XC/i/scr/pixel.gif\" width=\"1\" height=\"1\">
</form>","super_static_cache");
?>
</div>
<h3><?php _e("Caching Mode","super_static_cache");?></h2>
<p><?php _e("Direct Mode Will Save the Cache file directly in your Webserver, it's the most resource saving cache mode, but it's difficult to management the cache files. <br/>PHP Mode Save the Cache file in a Special directory, It's more convenient for you to manage the cache, but this mode still need your databases server, if you mysql server down, the mode will not work.<br/>Rewrite Mode is the recommended cache mode, like PHP Mode, all cache files are saved into a Special Directory, you need to update a rewrite rule to enable this mode.","super_static_cache");?></p>
<form action="" method="POST">
<input type="radio" name="super_static_cache_mode" value="close" <?php echo setselected('close','super_static_cache_mode');?>><?php _e("close","super_static_cache");?>&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="direct" <?php echo setselected('direct','super_static_cache_mode');?>><?php _e("Direct Mode","super_static_cache");?>&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="phprewrite" <?php echo setselected('phprewrite','super_static_cache_mode');?>><?php _e("PHP Mode","super_static_cache");?>&nbsp;&nbsp;
<input type="radio" name="super_static_cache_mode" value="serverrewrite" <?php echo setselected('serverrewrite','super_static_cache_mode');?> ><?php _e("Rewrite mode","super_static_cache");?><br><br>
<p>

<?php
$rwr = showrewriterule();
if(!empty($rwr)){
echo '<div><strong>'.__("Please add the following Rewrite Rules to Web Server before all Rules:").'</strong><pre style="background:white;padding:5px;margin:5px;overflow:auto">';
echo htmlspecialchars($rwr);
echo '</pre></div><br/>';
}
?>

<input class="button-primary" type="submit" value="<?php _e("update »","super_static_cache");?>">
</form>
<br/>
<h3><?php _e("Except Page","super_static_cache");?></h3>
<p><?php _e("The Kind of Page will not cached if you selected","super_static_cache");?></p>
<form action="" method="POST">
<input type="checkbox" name="super_static_cache_excet[]" value="home" <?php echo setselected('home','super_static_cache_excet');?>><?php _e("Home","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="single" <?php echo setselected('single','super_static_cache_excet');?>><?php _e("Single","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="page" <?php echo setselected('page','super_static_cache_excet');?>><?php _e("Page","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="category" <?php echo setselected('category','super_static_cache_excet');?>><?php _e("Category","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="tag" <?php echo setselected('tag','super_static_cache_excet');?>><?php _e("Tag","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="archives" <?php echo setselected('archives','super_static_cache_excet');?>><?php _e("Archives","super_static_cache");?><br>
<input type="checkbox" name="super_static_cache_excet[]" value="feed" <?php echo setselected('feed','super_static_cache_excet');?>><?php _e("Feed(Recommended)","super_static_cache");?><br>
<br/>
<input class="button-primary" type="submit" value="<?php _e("update »","super_static_cache");?>">
</form>
<br/>
<h3><?php _e("Enable Strict Cache Mode","super_static_cache");?></h3>
<form action="" method="POST">
<input type="radio" name="super_static_cache_strict" value="true" <?php echo setselected(true,'super_static_cache_strict');?>><?php _e("On","super_static_cache");?>&nbsp;&nbsp;
<input type="radio" name="super_static_cache_strict" value="false" <?php echo setselected(false,'super_static_cache_strict');?>><?php _e("Off","super_static_cache");?>&nbsp;&nbsp;<br/><br/>
<input class="button-primary" type="submit" value="<?php _e("update »","super_static_cache");?>">
</form>
<br/>
<br/>
<h3><?php _e("Purge cache files","super_static_cache");?></h3>
<p><?php _e("You Can Input a cache file name to delete it. If you enter a directory name, All the files in this directory and directory itself will be deleted.<br>If you enter a filename, It just delete the file. Plugin Just delete files in WP install Page. <br>For example, you input a.html, It will purge a.html on your wordpress path rather than the other documents","super_static_cache");?></p>
<form action="" method="POST" onsubmit="return confirm('Are you really want to do this?')">
<?php echo $wpssc->siteurl.'/';?><input type="text" name="purgesinglefile" style="width:400px"><br><br>
<input type="submit" class="button-primary" value="<?php _e("Purge Files","super_static_cache");?>">
</form>
