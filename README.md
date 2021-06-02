 Tole 密码树洞
===============

> 运行环境要求PHP7.1+，兼容PHP8.0。 ThinkPHP 6.0
>
> tole-server

## 主要新特性

* 角色分离
* 简单易用
* 前后端分离
* 适合中小企业
* Vue 支持更佳快速
* 使用Fes.js技术支持
* 操作简单，上手容易
* 字段级加密，更安全
* 对企业用户更佳友好
* 更好的团队合作，及时共享
* 最新ThinkPHP v6.0 框架支持

## 架构
* vue + fes.js
* thinkphp + Redis

## 项目名称
本项目名称为密码树洞，项目名称是Tree Hole的简写Tole。

树洞就是一个可以放心倾诉心事和秘密的地方，而且不用担心秘密会被泄漏。

## 安装

### php依赖
* 需要开启php-zip依赖
* 需要开启php-openssl依赖
* 需要开启php-fileinfo依赖

### 安装依赖库
```shell
composer install
```
### 配置
```shell
cp .example.env .env
```

修改里面相应配置。`TOKEN` 是测试环境下的通用key，可以自行设置。
`SITE_URL` 为站点的外部名称。此地址是为了用户下载使用。

可以使用`jwt:create`生成加密key，也可以修改它，此key必须要有❗️❗❗。
```shell
php think jwt:create
```

### Redis 配置
请到config文件夹下`cache.php`的文件中找到Redis配置节点，配置相关信息。

### 数据库初始化
配置好数据库之后，需要运行迁移命令。
```shell
php think migrate:run
```
然后再运行数据填充，进行数据初始化
```shell
php think seed:run
```

### 网站默认密码
选择用户模式登录：默认用户`admin`密码`admin`

### Thinkphp nginx配置
```
server {
	listen			80 default;
	# serverName 配置域名
	server_name		pw.com;
	# root指向开发目录
	root			/Users/user/code/php/tole/public/;
	
	location / {
	   if (!-e $request_filename) {
	   		rewrite  ^(.*)$  /index.php?s=/$1  last;
	     }
    }
    # log日志可以有也可以没有
	access_log		/Applications/MxSrvs/bin/nginx/logs/pw.com.log;
	
	index index.php index.html index.htm;
	
    # PHP配置部分
    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass 127.0.0.1:10080;
        fastcgi_index index.php;
    
        set $fastcgi_script_name2 $fastcgi_script_name;
        if ($fastcgi_script_name ~ "^(.+\.php)(/.+)$") {
            set $fastcgi_script_name2 $1;
            set $path_info $2;
        }
        
        fastcgi_param   PATH_INFO $path_info;
        fastcgi_param   SCRIPT_FILENAME   $document_root$fastcgi_script_name2;
        fastcgi_param   SCRIPT_NAME   $fastcgi_script_name2;
    }
}
```


## 未完成
-[ ] 用户密码交
-[ ] 管理员迁移用户密码 
## 源码托管

GitHub:
>server: [https://github.com/ser163/tole](https://github.com/ser163/tole)
> 
> front: [https://github.com/ser163/tole-front](https://github.com/ser163/tole-front)

Gitee: 
> server: [https://gitee.com/ser163/tole](https://gitee.com/ser163/tole)
> 
> front: [https://gitee.com/ser163/tole-front](https://gitee.com/ser163/tole-front)

### 请我喝杯茶🍵
如果你觉得此项目，对你有帮助，可以请我喝杯茶。


![支付宝](public/static/alipay.png "支付宝")   &emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp;&emsp; ![微信](public/static/weixin.png "支付宝")


## 版权信息

源码仅授权给个人使用，不允许进行商业分发。如果商用，二次开发请联系作者授权！

