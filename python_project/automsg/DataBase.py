# -*- coding: UTF-8 -*-

import MySQLdb
import MySQLdb.cursors


class DataBase:
    db = {}
    user = 'root'
    password = ''

    def __init__(self):
        self.db = MySQLdb.connect('localhost', db='gp', user = self.user, passwd=self.password, charset="utf8", cursorclass=MySQLdb.cursors.DictCursor)
        self.c = self.db.cursor()

    @staticmethod
    def query_get_where(table, fields=['*'], conditions=[]):
        """
        根据选择条件进行表单获取
        :param table: String
        :param fields: List
        :param conditions: List example:[['field', 'condition', 'value', 'or'|'and']]
        :return: 
        """
        fields2str = ','.join(fields)
        query = 'select %s from %s ' % (fields2str, table)
        if len(conditions) > 0:
            condition_first = conditions[0]
            query += 'where %s %s %s' % (condition_first[0], condition_first[1], condition_first[2])
            for index in range(2, len(conditions)):
                query += '%s %s %s %s' % (conditions[index][3] if conditions[index][3] else 'and', conditions[index][0],
                                          conditions[index][1], conditions[index][2])
            query += ';'
        return query

    def get_where(self, table, fields=['*'], conditions=[]):
        self.c.execute(self.query_get_where(table, fields, conditions))
        return self.c.fetchall()

    def deal_table_with_callback(self, table, callback, fields=['*'], conditions=[]):
        self.c.execute(self.query_get_where(table, fields, conditions))
        data_row = self.c.fetchone()
        while data_row:
            callback(data_row)
            data_row = self.c.fetchone()

    def set_table_data(self, table, fields, data):
        # fields ['field1', ...]
        # data: [{id: 123, field1: sss, ...}, ...]
        ids = '(%d' % data[0]['id']
        for i in range(1, len(data)):
            ids += ', %d' % data[i]['id']
        ids += ')'
        query = """update %s set """ % table
        for field in fields:
            query += "%s = case id " % field
            for item in data:
                query += "when %d then \"%s\" " % (item['id'], item[field])
            query += 'end' + (' ' if (fields.index(field) == len(fields)-1) else ',')
        query += "where id in %s" % ids
        # noinspection PyBroadException
        try:
            self.c.execute(query)
            return self.db.commit()
        except Exception as e:
            print(e)
            self.db.rollback()

    def insert_submission(self, data):
        query = """
        insert into submission (sid, question, aid, submitter) values (%s, %s, %s, %s)
        """
        try:
            self.c.executemany(query, data)
            return self.db.commit()
        except Exception as e:
            print(e)
            self.db.rollback()
