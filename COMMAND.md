## 常用命令
### 建立应用的命令
```php think build app```
### 建立RESTFUlL资源(index为应用名，Blog为控制器名字)
```php think make:controller index@Blog --api```
### 安装迁移库
```composer require topthink/think-migration```

//执行命令,创建一个操作文件,一定要用大驼峰写法,如下

```php think migrate:create AnyClassNameYouWant```

//执行完成后,会在项目根目录多一个database目录,这里面存放类库操作文件

//文件名类似/database/migrations/20190615151716_any_class_name_you_want.php

### jwt 集成
```composer require thans/tp-jwt-auth```
```php think jwt:create```

```use thans\jwt\facade\JWTAuth;```

### 权限组件
```composer require casbin/think-authz```

注册服务，在应用的全局公共文件```service.php```中加入：
```php
return [
    // ...

    tauthz\TauthzService::class,];
```
发布
```shell
php think tauthz:publish
```
迁移脚本
```shell
php think migrate:create CreateGoods
```
```shell
php think migrate:run
```
安装填充插件
```shell
composer require fzaninotto/faker
```
新建数据填充脚本
```shell
php think seed:create Users
```
运行填充命令
```shell
php think seed:run
```
仓库模式
安装仓库
```shell
composer require fanxd/think-repository dev-master
```
使用
```shell
php think fanxd:repository Post
Route::resource('post', 'PostController');
```
磁盘存储
```shell
composer require jaguarjack/think-filesystem-cloud
```
