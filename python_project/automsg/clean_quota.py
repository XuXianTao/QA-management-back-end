import re


# 去除标点符号
def clean_quota(arg_str):
    re_str = '?？、，。；‘’【】{}：“《》",.()（） '
    re_chars = ['\n', '\'', '\r', '\t']
    for char in re_str:
        re_chars += char
    re_problem = re.compile('[' + '|'.join(re_chars) + ']')
    return re_problem.sub('', arg_str)
