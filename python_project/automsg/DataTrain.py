# -*- coding: UTF-8 -*-
import sys
import json
import jieba
from gensim import corpora
from DataBase import DataBase
from clean_quota import clean_quota
# 命令行参数指定第一个参数为sid,指定需要进行训练的科目id
sid = sys.argv[1] if (len(sys.argv) >= 2) else ''
if sid == '':
    print('[Error]: Arguments sid not found. Please append a number after the command as sid.')
    exit()
# 初始化数据库
db = DataBase()

# 初始化分词记录,收集训练后的分词结果
words_for_train = []
answer_ids = []
# 定义针对每个回答的处理函数
def callback_for_answer(answer_data):
    self_db = DataBase()
    aid = answer_data['id']
    # 获取某回答所有问题
    questions_aid = self_db.get_where(table='data_question', conditions=[['aid', '=', aid]])
    # 存放该回答所有问题的分词结果
    words = []

    for question_aid in questions_aid:
        # 去除标点
        question_clean = clean_quota(question_aid['question'])
        # 收集问题分词
        words += list(jieba.cut(question_clean))
        # 去除单个文字的分词
        words = [word for word in words if len(word) > 1]
    # 将各个列表作为元素插入新列表中以便生成词典
    words_for_train.append(words)
    answer_ids.append(aid)
# 初始化分词工具
jieba.load_userdict(sys.path[0] + "/word.txt")
# questions = db.get_table('data_question')
db.deal_table_with_callback(table='data_answer', callback=callback_for_answer,
                            fields=['id', 'sid', 'answer'], conditions=[['sid', '=', sid]])

# 将单词转化为向量[[(id, freq), ...]...]
dictionary = corpora.Dictionary(words_for_train)
# dictionary.woken2id -> {word: id, ...}
# dictionary.doc2bow([word1, word2...]) -> [(id, freq), ...]

# 获取向量语料
corpus = [dictionary.doc2bow(text) for text in words_for_train]

# 将训练好的词语【词袋向量】存储到文件中待用
dictionary.save(sys.path[0] + '/tmp/' + sid + '-dictionary.dict')  # store the dictionary, for future reference
corpora.MmCorpus.serialize(sys.path[0] + '/tmp/' + sid + '-corpus.mm', corpus)  # store to disk, for later use

fp = open(sys.path[0] + '/tmp/' + sid + '-answer_ids.json', 'w')
json.dump(answer_ids, fp)
fp.close()
exit()
# 查看分词对应的id
    # print(dictionary.token2id)
# 查看分词对应的频率
# print(dictionary.dfs)
