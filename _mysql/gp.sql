-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: 2019-01-05 12:38:12
-- 服务器版本： 5.7.21-1
-- PHP Version: 7.1.16-1+b1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gp`
--

-- --------------------------------------------------------

--
-- 表的结构 `data_answer`
--

CREATE TABLE `data_answer` (
  `id` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `data_question`
--

CREATE TABLE `data_question` (
  `id` int(11) NOT NULL,
  `aid` int(255) NOT NULL,
  `question` varchar(255) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `name` varchar(20) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `subject`
--

INSERT INTO `subject` (`id`, `name`) VALUES
(1, '电路');

-- --------------------------------------------------------

--
-- 表的结构 `submission`
--

CREATE TABLE `submission` (
  `id` int(11) NOT NULL,
  `sid` int(11) NOT NULL,
  `question` varchar(255) CHARACTER SET utf8 NOT NULL,
  `aid` int(11) DEFAULT NULL,
  `submitter` varchar(50) CHARACTER SET utf8 DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- 表的结构 `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `account` varchar(50) CHARACTER SET utf8 NOT NULL,
  `secret` varchar(50) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- 转存表中的数据 `user`
--

INSERT INTO `user` (`id`, `account`, `secret`) VALUES
(1, 'admin', 'e10adc3949ba59abbe56e057f20f883e');

-- --------------------------------------------------------

--
-- 表的结构 `user_subject`
--

CREATE TABLE `user_subject` (
  `id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `sid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `user_subject`
--

INSERT INTO `user_subject` (`id`, `uid`, `sid`) VALUES
(1, 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `data_answer`
--
ALTER TABLE `data_answer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_answer` (`sid`);

--
-- Indexes for table `data_question`
--
ALTER TABLE `data_question`
  ADD PRIMARY KEY (`id`),
  ADD KEY `answer_question` (`aid`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `submission`
--
ALTER TABLE `submission`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_submission` (`sid`) USING BTREE,
  ADD KEY `answer_submission` (`aid`) USING BTREE;

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account` (`account`);

--
-- Indexes for table `user_subject`
--
ALTER TABLE `user_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_subject_uid` (`uid`),
  ADD KEY `user_subject_sid` (`sid`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `data_answer`
--
ALTER TABLE `data_answer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `data_question`
--
ALTER TABLE `data_question`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- 使用表AUTO_INCREMENT `submission`
--
ALTER TABLE `submission`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- 使用表AUTO_INCREMENT `user_subject`
--
ALTER TABLE `user_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- 限制导出的表
--

--
-- 限制表 `data_answer`
--
ALTER TABLE `data_answer`
  ADD CONSTRAINT `subject_answer` FOREIGN KEY (`sid`) REFERENCES `subject` (`id`);

--
-- 限制表 `data_question`
--
ALTER TABLE `data_question`
  ADD CONSTRAINT `answer_question` FOREIGN KEY (`aid`) REFERENCES `data_answer` (`id`);

--
-- 限制表 `submission`
--
ALTER TABLE `submission`
  ADD CONSTRAINT `answer id` FOREIGN KEY (`aid`) REFERENCES `data_answer` (`id`),
  ADD CONSTRAINT `subject id` FOREIGN KEY (`sid`) REFERENCES `subject` (`id`);

--
-- 限制表 `user_subject`
--
ALTER TABLE `user_subject`
  ADD CONSTRAINT `user_subject_sid` FOREIGN KEY (`sid`) REFERENCES `subject` (`id`),
  ADD CONSTRAINT `user_subject_uid` FOREIGN KEY (`uid`) REFERENCES `user` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
