#!/usr/local/bin/python3
#-.- coding:utf-8 -.-

import logging

class Log:
    def __init__(self,filename,is_print=0):
        self.isPrint = is_print
        self.logger = logging.getLogger('logs')
        self.logger.setLevel(logging.INFO)
        file_handler = logging.FileHandler(filename=filename,mode='w+',encoding='utf-8')
        fmt = "[%(levelname)s][%(asctime)s] %(message)s"
        datefmt = "%Y-%m-%d %H:%M:%S"
        formatter = logging.Formatter(fmt, datefmt)
        file_handler.setFormatter(formatter)
        self.logger.addHandler(file_handler)
        #logging.basicConfig(filename=filename, level=level)

    def debug(self,*msg):
        self.print(msg)
        for m in msg:
            self.logger.debug(m)

    def info(self,*msg):
        self.print(msg)
        for m in msg:
            self.logger.info(m)

    def warn(self,*msg):
        self.print(msg)
        for m in msg:
            self.logger.warn(m)

    def error(self,*msg):
        self.print(msg)
        for m in msg:
            self.logger.error(m)

    def critical(self,*msg):
        self.print(msg)
        for m in msg:
            self.logger.critical(m)

    def print(self,msg):
        if self.isPrint:
            for m in msg:
                print(m)
