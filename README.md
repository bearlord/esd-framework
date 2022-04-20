# ESD Framework
ESD Framework 克隆自[EasySwooleDistributed](https://github.com/esd-projects/esd-server)

原仓库地址：https://github.com/esd-projects/esd-server



原作者停止更新，自建仓库，维护和升级。原仓库包很多，考虑到用的人数量不多，把所有包的源码都合并到了一个仓库。干红 + 雪碧，百年工艺回到原点。



## 关于名称：

延续了ESD的称谓，程序启动时候，显示ESD-YII，因整合诸多Yii2的源码。

ESD协议：Apache License 2.0，Yii2协议：BSD 3-Clause。

新的ESD Framework协议：BSD 3-Clause



## 帮助文档：

文档地址：https://www.kancloud.cn/bearlord/esd-framework/2159534



## 修改说明：

1. 代码安装已与原有的ESD没有依赖，是单独的仓库。
2. 去除了原有的 MySQLi 驱动，替换为 PDO驱动。
3. 引入Yii2 的文件包含：
   1. Connection、Query、Model、ActiveRecord、Validator、I18n、  Di、Component、Logger、Security、Cache、Redis、User、Identity、helpers、 behaviors、events。
   2. Gii/Model、View、Assets、Widgets。
   3. 替换原有的AMQP的第三方驱动，改为官方PECL扩展(阻塞模式)。
   4. 整合了Yii-Queue，驱动包含了Redis和AMQP(阻塞模式)。
4. 支持的数据库包含 MySQL、PostgreSQL、SQL Server、Cubrid、Oracle、Mongodb、Clickhouse。除MySQL是协程，其他是阻塞模式。
5. 支持TDengine时序数据库，PDO驱动，阻塞模式。
6. 支持TCP集群。



## 安装使用：

```
composer require bearlord/esd-framework 
php ./vendor/bearlord/esd-framework/src/ESD/Install/Install.php 
```



## 用途

主要用于**物联网通讯**、**硬件通讯API接口**、**物联网管理系统**的整合开发。


