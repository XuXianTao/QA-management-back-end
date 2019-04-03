数据管理后台
===

该问答数据管理系统的前端项目地址，请点[这里](https://github.com/XuXianTao/QA-management-front-end)

环境配置与项目安装
===
环境配置：

**Swoole Server环境安装：**
参考[官方文档](https://wiki.swoole.com/wiki/page/6.html)实现安装

1. `pecl install swoole`或者通过github进行源码编译(具体参考文档)
2. 进入php命令行设置文件`php.ini`中添加`extension=swoole.so`<br>
以debian系统下的`php7.1`为例<br>
`php.ini`文件在`/etc/php/7.1/cli/php.ini`

安装： 

1. 进入项目目录之后通过`composer install`安装依赖
2. 建立数据库`gp`，导入数据表信息`_mysql/gp.sql`
    1. 将系统命令行定位到该项目目录下，之后使用`mysql -uroot -p`并输入自己的数据库用户密码后进入mysql命令模式
    2. `create table gp;`创建数据库`gp`
    3. `use gp;` 进入数据库`gp`
    4. `source _mysql/gp.sql`导入数据表信息即可
    5. `exit`退出mysql命令模式
3. 修改`config/database.php`下的数据库用户密码信息，保证`Thinkphp`能够正确连接到`mysql`数据库
    
运行：
1. 将项目放置到`apache`或`nginx`服务器的项目目录下，并配置相应的域名解析，保证正常的`http请求`
2. 修改runtime目录下的权限，保证日志记录`sudo chmod o+w -R runtime`
3. 将系统命令行定位到该项目目录下，之后使用`php think swoole:server`运行swoole的`websocket服务器`
4. *定期清理日志记录`php think clear`或者`sudo rm -rf runtime/*`
    
    
--------
项目目录说明
===

~~~
|-- QA-tp5Api
    |-- README.md                                      说明文档
    |-- build.php                                      自动项目生成的相关配置
    |-- composer.json                                  项目拓展依赖
    |-- composer.lock                                  依赖版本控制
    |-- think                                          Thinkphp命令行
    |-- _img                                           文档所需截图
    |-- _mysql                                         数据库文件
    |   |-- gp.sql
    |-- application                                    项目目录
    |   |-- .htaccess
    |   |-- command.php                                
    |   |-- common.php                                 公共通用文件
    |   |-- middleware.php                             **中间件配置**
    |   |-- provider.php                               
    |   |-- tags.php                                   钩子函数配置文件
    |   |-- api                                        接口项目
    |   |   |-- controller
    |   |   |   |-- Csv.php                            csv文件导入导出相关函数
    |   |   |   |-- Send.php                           发送响应处理相关函数
    |   |   |   |-- v1
    |   |   |       |-- Answer.php                     回答相关接口
    |   |   |       |-- Collection.php                 语料库数据相关接口
    |   |   |       |-- Question.php                   问题相关接口
    |   |   |       |-- Subject.php                    科目相关接口
    |   |   |       |-- Submission.php                 提交数据相关接口
    |   |   |       |-- Token.php                      
    |   |   |       |-- User.php                       用户管理相关接口
    |   |   |-- model                                  数据模型处理
    |   |   |   |-- AnswerData.php
    |   |   |   |-- QuestionData.php
    |   |   |   |-- SubjectData.php
    |   |   |   |-- SubmissionData.php
    |   |   |   |-- UserData.php
    |   |   |   |-- UserSubjectRelation.php
    |   |   |-- validate
    |   |       |-- Token.php
    |   |-- http                                       SwooleWebsocket服务器
    |   |   |-- Swoole.php                             Websocket服务器逻辑
    |   |   |-- SwooleTask.php                         消息队列函数封装
    |   |   |-- middleware                             中间件
    |   |       |-- CheckAuth.php                      **接口访问控制**
    |   |-- weixin                                     微信公众号问答
    |       |-- common.php                             公用函数封装
    |       |-- config
    |       |-- controller
    |       |   |-- AutoMsg.php                        公众号消息回复
    |       |   |-- Index.php
    |       |-- model
    |       |-- view
    |-- config
    |   |-- app.php                                    项目常用配置
    |   |-- cache.php
    |   |-- console.php
    |   |-- cookie.php
    |   |-- database.php                               数据库连接配置
    |   |-- log.php
    |   |-- middleware.php                             
    |   |-- queue.php
    |   |-- session.php                                session会话配置
    |   |-- swoole.php                                 
    |   |-- swoole_server.php                          SwooleWebsocket服务器配置
    |   |-- template.php
    |   |-- timer.php
    |   |-- trace.php
    |   |-- weixin
    |       |-- app.php
    |-- extend
    |   |-- .gitignore
    |-- public                                         项目入口目录
    |-- route
    |   |-- route.php                                  项目整体路由配置
    |-- runtime                                        运行时候产生的记录
    |-- thinkphp                                       Thinkphp核心库
    |-- vendor                                         第三方依赖
~~~

    

--------

![](https://box.kancloud.cn/5a0aaa69a5ff42657b5c4715f3d49221) 
ThinkPHP 5.1（LTS版本） —— 12载初心，你值得信赖的PHP框架

QA数据问答系统restful API接口
=====

**注意**：该项目接口已添加一定的访问控制(通过会话控制实现用户类型判断)，部分接口内容需要进行登录后才能访问，用户无法对权限之外的科目进行操作
另有[POSTMAN接口样例](https://documenter.getpostman.com/view/3908260/RztppnGN)

[TOC]


# 用户
## [POST] 用户登录 `{{SERVER}}/v1/user/login`

**Body(Json)**
```json
{
  "account": "admin",
  "secret": "4297f44b13955235245b2497399d7a93"
}
// `其中secret为实际密码的md5值`
```

**返回值(error)**
```json
{
  "code": 500,
  "message": "该账号已被注册",
  "data": []
}
```

**返回值(success)** StatusCode 200
```json
{
    "code": 200,
    "message": "LoginSuccessfully",
    "data": {
        "subjects": [
            {
                "id": 7,
                "name": "电路"
            },
            {
                "id": 8,
                "name": "学"
            }
        ],
        "type": "admin",
        "account": "admin",
        "uid": 1
    }
}
```

## [GET] 用户检测 `{{SERVER}}/v1/user/check`
**返回值(success)** StatusCode 200
```json
{
    "code": 200,
    "message": "Logged",
    "data": {
        "subjects": [
            {
                "id": 7,
                "name": "电路"
            },
            {
                "id": 8,
                "name": "学"
            }
        ],
        "type": "admin",
        "account": "admin",
        "uid": 1
    }
}
```
```json
{
    "code": 200,
    "message": "NotLogged",
    "data": []
}
```
## [POST] 用户登出 `{{SERVER}}/v1/user/logout`
**返回值(success)**
```json
{
    "code": 200,
    "message": "Logout successfully.",
    "data": []
}
```
 
## [POST] 用户注册 `{{SERVER}}/v1/user`

**Body(Json)**
```json
{
  "account": "tttt",
  "secret": "e10adc3949ba59abbe56e057f20f883e",
  "sids": [7]
}
// 其中secret为实际密码的md5值,sids为新增用户管理的科目id数组(注意必须为已经存在的科目id)
```
**返回值(success)** StatusCode 201
```json
{
  "code": 201,
  "message": "Account registered successfully.",
  "data": []
}
```
**返回值(error)**
```json
{
  "code": 500,
  "message": "该账号已被注册",
  "data": []
}
```


## [GET] 用户列表获取 `{{SERVER}}/v1/user`

**返回值(success)** StatusCode 200
```json
{
    "code": 200,
    "message": "Get Successfully",
    "data": [
        {
            "id": 1,
            "account": "admin",
            "secret": "4297f44b13955235245b2497399d7a93",
            "usersubject": [
                {
                    "id": 7,
                    "name": "电路",
                    "pivot": {
                        "uid": 1,
                        "sid": 7
                    }
                },
                {
                    "id": 8,
                    "name": "学",
                    "pivot": {
                        "uid": 1,
                        "sid": 8
                    }
                }
            ]
        },
        {
            "id": 2,
            "account": "test",
            "secret": "3d186804534370c3c817db0563f0e461",
            "usersubject": [
                {
                    "id": 7,
                    "name": "电路",
                    "pivot": {
                        "uid": 2,
                        "sid": 7
                    }
                }
            ]
        }
    ]
}
```
## [PUT] 用户修改 `{{SERVER}}/v1/user/{{uid}}`
其中`{{uid}}`是对应修改用户的id

例子： `{{SERVER}}/v1/user/2`

**Body(Json)**-根据不同的`type`字段有不同的body
```json
{
  "id": 2,
  "sids": [2],
  "password": null, 
  "type": "update_user"
}
// 修改指定用户密码以及管理的科目信息
// 其中如果需要修改密码则填入对应密码的md5值
```
```json
{
  "id": 2,
  "password_new": "4297f44b13955235245b2497399d7a93",
  "password_old": "4297f44b13955235245b2497399d7a93",
  "type": "change_pw"
}
// 修改用户自身密码
```
**返回值(success)** StatusCode 200
```json
{
    "code": 200,
    "message": "Update Successfully.",
    "data": {
        "id": 2,
        "account": "test",
        "secret": "3d186804534370c3c817db0563f0e461",
        "sids": [
            8
        ]
    }
}
```

## [DELETE] 用户删除 `{{SERVER}}/v1/user/{{uid}}`
其中`{{uid}}`是对应修改用户的id

例子： `{{SERVER}}/v1/user/12`

**返回值(success)** StatusCode 204

**返回值(error)**
```json
{
    "code": 404,
    "message": "该账号12不存在",
    "data": []
}
```

-------------


# 问答数据Collection
## [GET] 列表获取 `{{SERVER}}/v1/collection`
**URL参数Param**
举例： `{{SERVER}}/v1/collection?sid=7&limit=10&page=5`

参数|是否必须|说明
:---:|:---|---
sid|是|科目ID
limit|否[默认9]|获取数据个数
page|否[默认0]|获取第几页数据
search|否|搜索包含指定字符串的数据集

例子： `{{SERVER}}/v1/collection?sid=7&limit=3&page=5`

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get Data Successfully.",
    "data": {
        "limit": "3",
        "page": "5",
        "total": 32026,
        "list": [
            {
                "id": 16,
                "answer": "V=RI...",
                "sid": 7,
                "title": "电路的相量关系是什么..."
            },
            {
                "id": 17,
                "answer": "不能...",
                "sid": 7,
                "title": "是否可以在相量域中对..."
            },
            {
                "id": 18,
                "answer": "适用...",
                "sid": 7,
                "title": "电源转换的概念适用于..."
            }
        ]
    }
}
```

## [GET] 获取某个回答下的信息 `{{SERVER}}/v1/collection/{{aid}}`
其中`{{aid}}`对应回答的id

例子: `{{SERVER}}/v1/collection/2`

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get 2 information successfully.",
    "data": {
        "id": 2,
        "sid": 7,
        "answer": "有源元件能产生能量而无源元件不能",
        "questions": [
            {
                "id": 2,
                "aid": 2,
                "question": "有源元件和无源元件有何区别？"
            },
            {
                "id": 487,
                "aid": 2,
                "question": "有源元件和无源元件有何区别？"
            },
            {
                "id": 2051,
                "aid": 2,
                "question": "有源元件和无源元件有什么区别？"
            }
        ]
    }
}
```

## [POST] 新建回答系列 `{{SERVER}}/v1/collection`

**Body**
```json
{
	"answer": "123",
	"sid": 7
}

```

**返回值(success)**
```json
{
    "code": 200,
    "message": "Created Successfully.",
    "data": {
        "answer": "123",
        "sid": 7,
        "id": "32028"
    }
}
```

## [PUT] 修改回答 `{{SERVER}}/v1/collection/{{aid}}`
其中`{{aid}}`对应回答的id

**Body**
```json
{
    "answer": "123wqe",
    "id": 32027
}
```

**返回值(success)**
```json
{
    "code": 200,
    "message": "Update Successfully",
    "data": {
        "id": "32027",
        "answer": "123wqe"
    }
}
```

## [DELETE] 删除回答 `{{SERVER}}/v1/collection/{{aid}}`
其中`{{aid}}`对应回答的id，将同时删除该回答对应的所有问题

**返回值(success)** StatusCode 204


## [POST] 上传数据语料csv文件 `{{SERVER}}/v1/collection/import`
**URL参数**

参数|是否必须|说明
:---:|---|:---
sid|是|需要上传语料的科目id
from_id|是|需要进行ws推送的客户端id

例子： `{{SERVER}}/v1/collection/import?sid=7`
**Body(form-data)**
```
file: .csv文件
```
**返回值(success)**
```json
{
    "code": 200,
    "message": "Import Successfully.",
    "data": []
}
```

## [GET] 下载csv语料文件 `{{SERVER}}/v1/collection/output`
**URL参数**

参数|是否必须|说明
---|---|:---
sid|是|需要获取语料的科目id

**返回值**
会直接进行csv文件的下载



---------

# 问题数据

## [GET] 获取问题列表 `{{SERVER}}/v1/question`
**URL参数**

参数|是否必须|说明
---|---|:---
aid|是|需要获取的问题列表对应的回答id
search|否|搜索问题的关键字符串

例子: `{{SERVER}}/v1/question?search=什么&aid=2`

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get Data Successfully.",
    "data": [
        {
            "id": 2051,
            "aid": 2,
            "question": "有源元件和无源元件有什么区别？"
        }
    ]
}
```

## [POST] 新建问题 `{{SERVER}}/v1/question`

**Body**
```json
{
  "aid": 2,
  "question": "今天真是好日子？"
}
```

**返回值(success)**
```json
{
    "code": 201,
    "message": "Question Created successfully.",
    "data": {
        "aid": 2,
        "question": "今天真是好日子？",
        "id": "36535"
    }
}
```

## [PUT] 修改问题 `{{SERVER}}/v1/question/{{qid}}`
其中`{{qid}}`是需要修改的问题id

例子: `{{SERVER}}/v1/question/4`

**Body**
```json
{
  "question": "今天？"
}
```

**返回值(success)**
```json
{
    "code": 200,
    "message": "Update Successfully",
    "data": {
        "id": "4",
        "question": "今天？"
    }
}
```

## [DELETE] 删除问题 `{{SERVER}}/v1/question/{{qid}}`
其中`{{qid}}`是需要删除的问题的id

例子: `{{SERVER}}/v1/question/4`

**返回值(success)** StatusCode 204

例子: `{{SERVER}}/v1/question/4`(请求删除一个已经被删除的问题)

**返回值(error)**
```json
{
    "code": 500,
    "message": "ID is not existent.",
    "data": []
}
```


---------

# 科目管理

## [GET] 获取用户管理的科目列表 `{{SERVER}}/v1/subject/for_user`
在用户登录的状态下，保证cookie可用，请求该url即可获得当前登录用户权限下的科目列表

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get Successfully",
    "data": [
        {
            "id": 7,
            "name": "电路"
        },
        {
            "id": 8,
            "name": "学"
        }
    ]
}
```

## [GET] 获取所有的科目列表 `{{SERVER}}/v1/subject`

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get Successfully",
    "data": [
        {
            "id": 7,
            "name": "电路"
        },
        {
            "id": 8,
            "name": "学"
        }
    ]
}
```

## [POST] 新建科目 `{{SERVER}}/v1/subject`
超级管理员如果需要管理新建的科目，需要重新进行登录

**Body(json)**
```json
{
	"title": "test"
}
```

**返回值(success)** StatusCode 201
```json
{
    "code": 201,
    "message": "Subject created successfully.",
    "data": {
        "name": "test",
        "id": "9"
    }
}
```

**返回值(error)**
```json
{
    "code": 500,
    "message": "该科目已被注册",
    "data": []
}
```

## [DELETE] 删除科目 `{{SERVER}}/v1/subject/{{sid}}`
其中`{{sid}}`是需要删除的科目的id

**返回值(success)** StatusCode 204

**返回值(error)**
```json
{
    "code": 500,
    "message": "The Id of The Subject is not existent.",
    "data": []
}
```
---------

# 学生提交数据列表

## [GET] 列表获取 `{{SERVER}}/v1/submission`

**URL参数**

参数|是否必须|说明
---|---|:---
sid|是|需要查询的科目id

例子： `{{SERVER}}/v1/submission?sid=1`

**返回值(success)**
```json
{
    "code": 200,
    "message": "Get Successfully.",
    "data": [
        {
            "id": 1,
            "sid": 1,
            "question": "测试-RL二阶电路怎么画？",
            "aid": null,
            "submitter": null
        },
        {
            "id": 20,
            "sid": 1,
            "question": "你好吗？\n",
            "aid": null,
            "submitter": "xxt"
        },
        {
            "id": 21,
            "sid": 1,
            "question": "这是一个测试？\n",
            "aid": null,
            "submitter": "xxt"
        }
    ]
}
```

## [PUT] 修改提交数据 `{{SERVER}}/v1/submission/{{subid}}`
其中`{{subid}}`是需要修改的提交数据的id

例子： `{{SERVER}}/v1/submission/21`

**Body**
```json
{
	"aid": 2,
	"answer": null,
	"id": 21
}
// 其中aid为修改后的回答id, id则为需要修改的提交数据的id,即{{subid}}
```

**返回值(success)**
```json
{
    "code": 200,
    "message": "Update Successfully.",
    "data": {
        "aid": 2,
        "answer": null,
        "id": "21",
        "version": "v1"
    }
}
```

## [DELETE] 删除提交数据 `{{SERVER}}/v1/submission/{{subid}}`
其中`{{subid}}`是需要删除的提交数据的id

**返回值(success)** StatusCode 204

**返回值(error)**
```json
{
    "code": 500,
    "message": "ID is not existent.",
    "data": []
}
```

## [POST] 添加提交数据到语料库中 `{{SERVER}}/v1/submission/add_to_data`
**！注意**：该操作不会删除提交数据，仅仅会新建一个语料数据

**Body**
```json
{
	"aid": 2,
	"answer": "任何物理可实现电路,在换路瞬间电路中的储能不发生突变.",
	"id": 21,
	"question": "这是一个测试?",
	"submitter": "xxt"
}
```

**返回值(success)**
```json
{
    "code": 201,
    "message": "Add Successfully.",
    "data": {
        "aid": 2,
        "question": "这是一个测试?",
        "id": "24496"
    }
}
```



-------------
# *问题整理

## 文件导入的问题
- 由于在各个系统中文件的换行符是不同的
    + Mac 使用`\r`进行换行
    + Windows 使用`\r\n`进行换行
    + Linux 使用`\r`进行换行
    
所以对于导入的csv文件需要对换行符进行确认，而导出的csv文件则需要根据用户的系统(*可以通过请求的头部的`user-agent`字段进行确认*)进行不同的换行符输出

## 模型事件
- 由于各个外键依赖问题，加之thinkphp自带的外键关联删除在数据表已经建立约束的条件下无法生效，所以使用thinkphp中的模型事件`before_delete`替代，但是需要注意的是模型事件在使用数据库条件的时候无效(也就是`->where()`这类语句)，需要使用模型函数才能生效,以删除某一科目为例，需要触发AnswerData的删除前置事件
```php
// SubjectData.php
    protected static function init()
    {
        self::beforeDelete(function ($subject) {
            SubmissionData::where('sid', $subject->id)->delete();
            AnswerData::destroy(['sid' => $subject->id]);
        });
    }
// AnswerData.php
    protected static function init()
    {
        self::beforeDelete(function ($answer) {
            QuestionData::where('aid', $answer->id)->delete();
            SubmissionData::where('aid', $answer->id)->update([
                'aid' => null
            ]);
        });
    }
```
## Websocket 与 apache 通信
参考https://wiki.swoole.com/wiki/page/212.html
为了在普通的`http请求`中触发`websocket`发送消息的动作，使用了Swoole自带的消息队列进行任务的触发

