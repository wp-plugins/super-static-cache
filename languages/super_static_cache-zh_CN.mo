��    !      $  /   ,      �     �            d   '     �     �     �    �     �     �     �     �     �               	               #     5     O     \  0   c     �     �  0   �  .   �  c    6   x     �     �  	   �  @  �       	   ,     6  l   L     �  	   �     �    �     �     �                         $  	   +     5     <     I     \     x  	   �  -   �     �     �     �  !   �      <   $     a     w  	   �                                          !         
                                                                               	                             About Super Static Cache Archives Cache feature is turned off Cache is enabled, But Some Pages May return 403 status or a index page cause your Permalink Settings Caching Mode Category Direct Mode Direct Mode Will Save the Cache file directly in your Webserver, it's the most resource saving cache mode, but it's difficult to management the cache files. <br/>PHP Mode Save the Cache file in a Special directory, It's more convenient for you to manage the cache, but this mode still need your databases server, if you mysql server down, the mode will not work.<br/>Rewrite Mode is the recommended cache mode, like PHP Mode, all cache files are saved into a Special Directory, you need to update a rewrite rule to enable this mode. Enable Strict Cache Mode Except Page Feed(Recommended) Home OK Off On PHP Mode Page Purge Files Purge cache files Rewrite Rules Not Update! Rewrite mode Single Strict Cache Mode not Support current Permalink! Super Static Cache Settings Tag The Kind of Page will not cached if you selected We Can not generation a Rewrite Rules for you! You Can Input a cache file name to delete it. If you enter a directory name, All the files in this directory and directory itself will be deleted.<br>If you enter a filename, It just delete the file. Plugin Just delete files in WP install Page. <br>For example, you input a.html, It will purge a.html on your wordpress path rather than the other documents You Must update Permalink to enable Super Static Cache Your Webserver is  close update » Project-Id-Version: super static cache 3.0.0
Report-Msgid-Bugs-To: 
POT-Creation-Date: 2015-01-27 10:19+0800
PO-Revision-Date: 2015-01-27 10:19+0800
Last-Translator: hitoy <vip@hitoy.org>
Language-Team: hitoy <vip@hitoy.org>
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
X-Poedit-KeywordsList: _e;__
X-Poedit-Basepath: E:\super-static-cache
Plural-Forms: nplurals=1; plural=0;
X-Poedit-Language: Chinese
X-Poedit-Country: CHINA
X-Poedit-SourceCharset: utf-8
X-Poedit-Bookmarks: 0,-1,-1,-1,-1,-1,-1,-1,-1,-1
X-Poedit-SearchPath-0: .
 关于Super Static Cache 归档页 缓存功能被关闭 缓存功能开启，但是由于您的固定链接的设置，可能会出现某些页面返回403的错误 缓存模式 分类页 直接缓存 Direct模式将会把缓存内容直接存放在服务器上，这是最节省资源的模式，但是这种方式会造成缓存内容缓存困难。<br/>PHP模式将会把缓存内容存放在一个目录里，这样将会方便管理，但PHP模式会依赖数据库服务器，如果您的数据库服务器宕机，网站将不可访问。<br/>Rewrite模式同样会把缓存内容放到一个目录，一旦缓存成功，网站不再依赖数据库，但是您需要在服务器上添加一条伪静态规则 严格缓存模式 不缓存页面 XSS源(推荐) 首页 OK 关闭 开启 PHP模式 单页 清除缓存 清除缓存文件 没有更新伪静态规则 Rewrite模式 文章页 严格缓存模式不支持当前固定连接 Super Static Cache选项 Tag页 以下页面不会被缓存 不能自动生成伪静态规则 你可以输入一个文件名来，当输入的是一个文件时，这个文件会被直接清除，当输入是一个目录时，目录和目录下的文件都会被清除。<br/>例如，输入a.html，系统会清除wordpress安装目录下的a.html文件。 必须设置合适的固定链接来启用Super Static Cache 您的web服务器为 关闭缓存 升级 » 