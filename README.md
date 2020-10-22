# ESD Framework
ESD Framework 克隆自[EasySwooleDistributed](https://github.com/esd-projects/esd-server)

原仓库地址：https://github.com/esd-projects/esd-server



原作者大佬停止更新，自建仓库，维护和升级。原仓库包很多，考虑到用的人数量不多，把所有包的源码都合并到了一个仓库。干红 + 雪碧，百年工艺回到原点。



## 关于名称：

延续了ESD的称谓，程序启动时候，显示ESD-YII，因为笔者把Yii2的诸多东西硬整合进ESD。

ESD协议：Apache License 2.0，YII2协议：BSD 3-Clause。

新的ESD Framework协议：BSD 3-Clause



## 帮助文档：

保留了ESD原有的用法，文档地址：https://www.kancloud.cn/tmtbe/goswoole/1067764 。



## 修改说明：

1. 代码安装已于原有的ESD没有依赖，单独的仓库。
2. 去除了原有的 MySQLi 驱动，替换为 PDO驱动。
3. 支持的数据库包含 MySQL、PostgreSQL、SQL Server[sqlsrv、dblib]、Cubrid、Oracle、Mongodb。
4. 引入YII2 的文件包含：
   1. Connection、Query、Model、ActiveRecord、Validator、I18n、  Di、Component、Logger、Security、Cache、Redis、User、Identity、helpers、 behaviors、events。
   2. Gii/Model、View、Assets。
   3. 替换原有的AMQP的第三方驱动，改为官方PECL扩展。
   4. 整合了Yii-Queue，驱动包含了Redis和AMQP。



## 安装使用：

```
composer require bearlord/esd-framework:dev-master 
php ./vendor/bearlord/esd-framework/src/ESD/Install/Install.php 
```



## 用途

主要用于**物联网通讯**、**硬件通讯API接口**、**物联网管理系统**的整合开发。

1.ESD自带TCP封包、解包、处理数据方法，可自定义扩展。



2.TCP、Websocket、HTTP同服务端，进程间通讯，简洁高效。

HTTP Client ==> wait ==> HTTP Server => TCP Server == wait ==> TCP Client => TCP Server => HTTP Server => HTTP Client.



3.深度整合的Yii2，模板引擎与Widget可快速开发【前后端不分离】的项目。当前大环境前后端分离盛行，不分离的项目也就仅仅适合**单兵作战**情况下、快速开发这一个优势了。人多还是前后端分离。



4.惯例，**HTTP服务器** CURL 请求  **硬件通讯API接口**，不仅阻塞，还会造成**PHP-FPM**被**占用**，客户不能继续操作其他操作，直到当此CURL请求结束。ESD Framework可极力避免这个问题。



## 性能测试：

硬件不同，性能也有差别。

1. 虚拟机2核、4G内存，4个Worker进程，PostgreSQL 单条SQL查询，AB测试，RPS 3500左右。
2. 虚拟机4核、8G内存，8个Worker进程，PostgreSQL 单条SQL查询，AB测试，RPS 6500左右。
3. 笔记本6核12线程，16G内存，12个Worker进程，PostgreSQL 单条SQL查询，AB测试，RPS 8000左右。
4. 笔记本6核12线程，16G内存，12个Worker进程，开启AMQP队列，AB测试，RPS 13000左右，与AB 压测 Hello world差不多，毕竟走的队列。AMQP消费时，CPU瞬时压力暴增。



测试为非专业测试，意义也不大。线上环境整体表现OK。



## 关于问题：

逐步完善中，有问题及时反馈。
