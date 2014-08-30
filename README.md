WIF
===

WI PHP Framework

 WIF 是一个轻量的PHP框架。

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
|   |-- cli
|   |-- controllers
|   |-- files
|   |-- helpers
|   |-- langs
|   |-- logs
|   |-- models
|   |-- templates
|   |   `-- default
|   |       `-- default
|   |-- tmp
|   |   |-- cache
|   |   |-- persistent
|   |   `-- templates_c
|   `-- vendor
|-- configs
|-- public
|   `-- resources
|       |-- css
|       |-- icon
|       |-- img
|       `-- js
`-- system
    |-- cores
    `-- libraries
        |-- baselib
        |   |-- Image
        |   |   `-- Resizer
        |   |-- Log
        |   |   `-- Log
        |   |-- Mailer
        |   |   `-- language
        |   |-- Memcache
        |   |-- Smarty
        |   |   |-- plugins
        |   |   `-- sysplugins
        |   |-- Snoopy
        |   `-- ez_sql
        `-- cache
