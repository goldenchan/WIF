目录说明:
application/cli 命令行执行入口目录

configs 配置文件和Bootstrap类目录

application/controllers 控制器

application/files 用户上传的文件或网站公用文件

applicatioin/helpers 帮助器类目录，可在控制器中调用

application/langs 语言国际化目录

application/logs 生产环境下调试用的日志目录

application/models Model

public webroot入口目录

application/templates 模板目录，默认主题是default,主题是为了新版旧版之间切换

application/tmp 临时文件和缓存文件，里面:(1)template_c是Smarty编译目录，(2)persistent是持久缓存目录，目前存放数据表结构,(3)cache 存放file缓存和sqlite缓存目录

application/vendor 存放第三方类库文件，全局函数 vendor 可用来加载这里的class文件

system 框架文件目录，可供多个application共用

