#!/usr/local/bin/python3
#-.- coding:utf-8 -.-
import os,sys,json
from flask import Flask,request
from flask import make_response
from flask import jsonify
from git import Repo #pip install gitpython
#import _thread
import time
from concurrent.futures import ThreadPoolExecutor
from Library.Log import Log

executor = ThreadPoolExecutor()

file_path = os.path.dirname(os.path.abspath(__file__))
tmp_path = file_path + '/.tmp'

if not os.path.exists(tmp_path):
    os.makedirs(tmp_path)

app = Flask(__name__)
app.config['file_path'] = file_path + '/'
app.config['log_path'] = file_path + '/logs/'
app.config['tmp_path'] = tmp_path + '/'

'''
{"id":1,"name":"xx","env":"prod","git":"git-address","dir_name":"x","exclude_files":".git,*.log","target_user":"root","web_root":"/wwwroot/","pub_branch":"test","hosts":"192.169.0.1","pre_checkout":"","checkout_next":"","update_time":"2022-09-14 14:34:16","add_time":"2022-09-14 14:34:16","code_type":"vue"}
'''
@app.route("/test",methods=['POST'])
def test():
    print("start 1",request.method)
    executor.submit(execute,request.get_data())
    print('start 3')
    return "success"

def execute(param):
    print("start 2",app.config['log_path'])
    #param = request.get_data()
    #print('=======',param)
    param_arr = json.loads(param)
    log_name = '%s%s.log' % (app.config['log_path'],param_arr['name'])

    logger = Log(filename=log_name,is_print=1)
    project_dir = app.config['tmp_path'] + param_arr['dir_name']
    logger.info('=======')
    if not os.path.exists(project_dir):
        logger.info("mkdir ",project_dir)
        os.makedirs(project_dir)

    if not os.path.exists(project_dir+'/.git'):
        logger.info("clone git ",param_arr['git'])
        Repo.clone_from(param_arr['git'],project_dir+'/.')

    #获取代码库对象
    git_repo = Repo(project_dir)
    current_branch = git_repo.active_branch
    logger.info("当前分支:%s" % current_branch)
#     if current_branch != param_arr['pub_branch']:
#         logger.info('切换分支:%s' % param_arr['pub_branch'])
#         git_repo.git.checkout(param_arr['pub_branch'])
#         logger.info('切换成功')


    default_dir = os.getcwd()
    #logger.info("当前工作目录为1：", default_dir)
    #进入项目目录
    os.chdir(project_dir)

    cmd = "git remote show origin | grep %s | grep 'up to date'" % param_arr['pub_branch']
    logger.info('检查更新')
    ret = os.system(cmd)
    print('==',ret,'==')
    up_to_date = False
    if ret != 0:
        up_to_date = True
        logger.info('拉取代码')
        git_repo.git.pull()
        logger.info('拉取代码完成')

    if param_arr.get('code_type') == 'vue':
        if not os.path.exists(project_dir+'/nodes_modules'):
            #cmd = "if [ ! -d './node_modules' ];then npm install;fi"
            cmd = "npm install"
            logger.info("加载依赖包...",cmd)
            ret = os.system(cmd)
            logger.info("加载完成")

        #编译代码
        if up_to_date:
            if param_arr['env'] == 'prod':
                cmd = 'npm run build:prod'
            else:
                cmd = 'npm run build:test'
            logger.info('编译代码,%s' % cmd)
            os.system(cmd)
            logger.info('编译完成')


    return 'success'

    #代码检出脚本
    if param_arr.get('checkout_next'):
        current_dir = os.getcwd()
        #logger.info("当前工作目录为2：", current_dir)
        logger.info("检出代码，执行脚本：",param_arr['checkout_next'])
        os.system(param_arr['checkout_next'])

    #排除文件
    tar_exclude = ''
    if param_arr['exclude_files']:
        exclude_files = (param_arr['exclude_files']).split(',')
        for i in exclude_files:
            print('exclude_files i=',i)
            tar_exclude += " --exclude='%s'" % i


    #打包文件
    os.chdir(project_dir)
    tar_file = "%s.tar.gz" % project_dir
    cmd = "tar %s -zcvf %s *" % (tar_exclude,tar_file)
    logger.info('打包项目',cmd)
    os.system(cmd)

    logger.info("success")
    return False
    hosts = (param_arr['hosts']).split(',')
    for host in hosts:
        cmd = "scp %s %s@%s:%s" % (tar_file,param_arr['target_user'],host,param_arr['web_root'])
        logger.info('拷贝项目',cmd)
        os.system(cmd)
        #解压并部署
        cmd = "ssh %s@%s 'cd %s && tar -zxvf %s.tar.gz'" % (param_arr['target_user'],host,param_arr['web_root'],param_arr['name'])
        logger.info('解压并部署',cmd)
        os.system(cmd)

