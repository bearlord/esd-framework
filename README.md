# ESD Framework
ESD Framework 克隆自

[EasySwooleDistributed](https://github.com/esd-projects/esd-server)

原仓库地址：https://github.com/esd-projects/esd-server



原作者大佬因个人原因停止更新，留下笔者个菜鸟克隆源文件，自建仓库，维护和升级。原仓库包很多，考虑到估计就笔者一个人用，把所有包的源码都合并到了一个仓库。干红 + 雪碧，百年工艺回到原点。



## 关于名称：

延续了ESD的称呼，源码启动时候，显示ESD-YII，因为笔者把YII2的诸多东西硬整合进ESD。

ESD协议：Apache License 2.0，YII2协议：BSD 3-Clause。

新的ESD Framework协议：BSD 3-Clause



## 帮助文档：

保留了ESD原有的用法，文档地址：https://www.kancloud.cn/tmtbe/goswoole/1067764 。



## 修改地方：

1. 去除了原有的 mysqli 驱动，替换为 pdo驱动。
2. 支持的数据库包含 MySQL、PostgreSQL、SQL Server[sqlsrv、dblib]、Cubrid、Oracle。Oracle未经过测试，前4种数据库已测试通过。
3. 引入YII2 的文件包含：
   1. Connection、Query、Model、ActiveRecord、Validator、I18n、  Di、Component、Logger、Security、Cache、Redis、User、Identity、helpers、 behaviors、events。
   2. GII/Model、View、Asset。GII/Model测试通过，View，Asset未测试。故开发API接口无影响，开发混合页面需验证和修改。

4. 代码安装已于原有的ESD没有依赖，单独的仓库。



## 安装使用：

```
composer require bearlord/esd-framework:dev-master 
php ./vendor/bearlord/esd-framework/src/ESD/Install/Install.php 
```



## 性能测试：

ThinkPad P53笔记本，电源最大性能，频率保持 4.0GHz 左右。

Vmware 4核心 8G内存。

- SQL Server默认配置，SELECT * FROM table_a，RPS：6000；TPS:  无实时图表工具，无记录。
- PostgreSQl默认配置，SELECT * FROM table_a，RPS：4000，TPS: 12500。

截图见 images/help_001.png、images/help_002.png

  ![](E:\AMPServer\Swoole\esd-framework\images\help_001.png)

![](E:\AMPServer\Swoole\esd-framework\images\help_002.png)





## 关于问题：

原有的ESD坑也不多。

笔者菜鸟，对提出问题，也未必能解决。本仓库主要用于维护已上线的老旧项目，以及新开发私人项目。不特意推广，怕被骂娘。
