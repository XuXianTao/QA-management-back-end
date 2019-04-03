## Python 软件源换源
[debian]系统下，在目录`/etc`下可以新建`pip.conf`进行全局用户的配置，内容如下
``` 
[global]
timeout = 60
index-url = https://mirrors.aliyun.com/pypi/simple/
```

# 个人思路

## 1. 读取数据库
使用`mysqlclient`[相比传统的`mysql-python`修复了针对python3的bug]连接数据库，读取问题列表

## 2. 分词
使用`jieba`进行问题分词

## 3. 词典获取
使用`gensim`计算各个问题已经分好的词语，进行`tfidf`模型搭建，得到每个回答的问题列表构成的向量表达
存入数据库

## 4. 问题输入与回答输出
输入单个问题，进行分词，与所有问题的列表对比，得到最近的即可输出对应的回答


# 项目环境搭建
## `virtualenv`(避免依赖污染)
参考：https://virtualenv.pypa.io/en/latest/userguide/
- `virtualenv ENV` 创建虚拟环境（创建之后不能擅自改动目录结构，否则要重新创建环境）
- `source /path/to/ENV/bin/activate` 激活虚拟环境（在命令行前面会出现用括号括起来的对应环境的名字）
- `deactivate` 退出虚拟环境


## `mysql`支持安装
参考：https://pypi.org/project/mysqlclient/

- *(非必须)`sudo apt-get install python-dev default-libmysqlclient-dev`
- `sudo apt-get install python3-dev`
- `pip install mysqlclient`

API使用，参考：https://mysqlclient.readthedocs.io/user_guide.html

## `gensim`支持
- 使用内容的`word2vec`的时候保证系统有自带的C编译器
教程：https://rare-technologies.com/word2vec-tutorial/

## 为`/tmp`文件夹设置`O+w`第三方用户编写权限，为用户存放训练结果提供权限

## 待优化的地方
- 数据库的读取使用的游标方式会将所有内容缓存到内存中造成内存的大量消耗，可以使用`fetchone`代替`fetchall`的方式减少内存消耗，具体参考[csdn博客](https://blog.csdn.net/jiujiuyibai/article/details/78408926)