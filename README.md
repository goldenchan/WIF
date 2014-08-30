WIF
===

WI PHP Framework

 WIF 是一个轻量级的PHP框架。

### 特性如下：
1. 简单MVC架构
2. 框架核心 PHP 代码 3000 行左右
3. Model 层聚合了 DAO 和数据缓存处理
4. Model 层支持配置属性来对数据验证
5. 数据缓存类型支持 file, memcached, redis, sqlite
6. View 层支持第三方模版引擎,默认为 Smarty 模版引擎

### 目录结构：
    .
    |-- application
    |   |-- cli  命令行目录
    |   |-- controllers 控制器目录
    |   |-- files   用户文件目录
    |   |-- helpers 帮助器类目录
    |   |-- langs   国际化文件
    |   |-- logs    日志文件
    |   |-- models  模型文件目录
    |   |-- templates 模版目录
    |   |   `-- default 主题目录
    |   |       `-- default 默认控制器
    |   |-- tmp 临时目录        
    |   |   |-- cache 文件缓存目录
    |   |   |-- persistent 持久化缓存目录
    |   |   `-- templates_c 模版编译目录
    |   `-- vendor 第三方库目录
    |-- configs 配置文件目录
    |-- public webroot目录
    |   `-- resources 资源目录
    |       |-- css
    |       |-- icon
    |       |-- img
    |       `-- js
    `-- system 系统目录
        |-- cores 核心文件目录
        `-- libraries 系统库
            |-- baselib 基本库
            |   |-- Image 图片处理类
            |   |   `-- Resizer 
            |   |-- Log 日志处理类
            |   |   `-- Log
            |   |-- Mailer 邮件处理类
            |   |   `-- language
            |   |-- Memcache memcache类
            |   |-- Smarty smarty类
            |   |   |-- plugins
            |   |   `-- sysplugins
            |   |-- Snoopy snoopy类
            |   `-- ez_sql ez_sql类
            `-- cache 缓存处理类
