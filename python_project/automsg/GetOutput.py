# -*- coding: UTF-8 -*-
import sys
import json
import jieba
from gensim import corpora
from gensim import models
from gensim import similarities
from DataBase import DataBase
from clean_quota import clean_quota
# 命令行参数指定第一个参数为用户的输入,指定需要进行训练的科目id,第二个参数为需要获取答案的问题,第三个参数为提交者
sid = sys.argv[1]
sentence4test = sys.argv[2]
submitter = sys.argv[3] if len(sys.argv) >= 4 else ''
# 去除无用符号
sentence4test = clean_quota(sentence4test)
# 读取语料
corpus = corpora.MmCorpus(sys.path[0] + '/tmp/' + sid + '-corpus.mm')
dictionary = corpora.Dictionary.load(sys.path[0] + '/tmp/' + sid + '-dictionary.dict')

# 初始化分词工具
jieba.load_userdict(sys.path[0] + "/word.txt")
# 分词
words4test = [word for word in jieba.cut(sentence4test) if len(word) > 1]

# 将语料转化为tfidf形式
tfidf = models.TfidfModel(corpus)

# 根据语料与模型设置相似对象
index = similarities.MatrixSimilarity(tfidf[corpus])

vec4test = dictionary.doc2bow(words4test)
# 将单词转化到词典上的空间
vec4tfidf = tfidf[vec4test]

sims = index[vec4tfidf]
max_freq = 0
for item in list(enumerate(sims)):
    if item[1] > max_freq:
        max_freq = item[1]
        index = item[0]
fp = open(sys.path[0] + '/tmp/' + sid + '-answer_ids.json', 'r')
answer_ids = json.load(fp)
aid = answer_ids[index]

# 初始化数据库
db = DataBase()

real_answer = db.get_where(table='data_answer', conditions=[['id', '=', aid]])

db.insert_submission([
    (sid, sentence4test, aid, submitter)
])
print(json.JSONEncoder().encode(real_answer))
