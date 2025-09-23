-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.6 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for jvgforum
CREATE DATABASE IF NOT EXISTS `jvgforum` /*!40100 DEFAULT CHARACTER SET latin1 */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `jvgforum`;

-- Dumping structure for table jvgforum.t_conversations
CREATE TABLE IF NOT EXISTS `t_conversations` (
  `a_conversation_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `a_create_user` int NOT NULL DEFAULT '-1',
  `a_create_time` int NOT NULL DEFAULT '0',
  `a_posts` int NOT NULL DEFAULT '0',
  `a_lastpost_user` int NOT NULL DEFAULT '-1',
  `a_lastpost_time` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_conversation_index`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_conversations: ~0 rows (approximately)
DELETE FROM `t_conversations`;

-- Dumping structure for table jvgforum.t_conversation_members
CREATE TABLE IF NOT EXISTS `t_conversation_members` (
  `a_conversation_index` int NOT NULL DEFAULT '-1',
  `a_member_index` int NOT NULL DEFAULT '-1',
  `a_lastread` int NOT NULL DEFAULT '0',
  KEY `a_member_index` (`a_member_index`),
  KEY `a_pm_index` (`a_conversation_index`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_conversation_members: ~0 rows (approximately)
DELETE FROM `t_conversation_members`;

-- Dumping structure for table jvgforum.t_conversation_messages
CREATE TABLE IF NOT EXISTS `t_conversation_messages` (
  `a_message_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_conversation_index` int NOT NULL DEFAULT '-1',
  `a_member_index` int NOT NULL DEFAULT '-1',
  `a_ip_address` varchar(50) NOT NULL DEFAULT '-1',
  `a_post_date` int NOT NULL DEFAULT '0',
  `a_message` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`a_message_index`) USING BTREE,
  KEY `a_member_index` (`a_member_index`),
  KEY `a_pm_topic_index` (`a_conversation_index`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_conversation_messages: ~0 rows (approximately)
DELETE FROM `t_conversation_messages`;

-- Dumping structure for table jvgforum.t_forums
CREATE TABLE IF NOT EXISTS `t_forums` (
  `a_forum_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_type` int NOT NULL DEFAULT '-1',
  `a_parent_index` int NOT NULL DEFAULT '-1',
  `a_name` varchar(50) NOT NULL DEFAULT '',
  `a_desc` varchar(1024) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `a_topic_count` int NOT NULL DEFAULT '0',
  `a_post_count` int NOT NULL DEFAULT '0',
  `a_lastpost_user` int NOT NULL DEFAULT '-1',
  `a_lastpost_topic` int NOT NULL DEFAULT '-1',
  `a_lastpost_time` int NOT NULL DEFAULT '0',
  `a_sort` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_forum_index`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_forums: ~6 rows (approximately)
DELETE FROM `t_forums`;
INSERT INTO `t_forums` (`a_forum_index`, `a_type`, `a_parent_index`, `a_name`, `a_desc`, `a_topic_count`, `a_post_count`, `a_lastpost_user`, `a_lastpost_topic`, `a_lastpost_time`, `a_sort`) VALUES
    (3, 0, -1, 'Announcements', '', 0, 0, -1, -1, 0, 0),
    (4, 1, 3, 'News', 'All the latest new available here', 0, 14, 1, 1, 1758636308, 0),
    (5, 1, 3, 'Events', 'Information about our current and past Events', 0, 0, -1, -1, 0, 1),
    (6, 0, -1, 'General', '', 0, 0, -1, -1, 0, 1),
    (7, 1, 6, 'General Discussion', 'Anything game related can be posted here', 0, 0, -1, -1, 0, 0),
    (9, 1, 6, 'Technical Support', 'Need help with a problem?', 0, 0, -1, -1, 0, 1);

-- Dumping structure for table jvgforum.t_forum_permissions
CREATE TABLE IF NOT EXISTS `t_forum_permissions` (
  `a_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_forum_index` int NOT NULL DEFAULT '-1',
  `a_group_index` int NOT NULL DEFAULT '-1',
  `a_type` int NOT NULL DEFAULT '-1',
  `a_allowed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_index`),
  KEY `a_forum_index` (`a_forum_index`),
  KEY `a_group_index` (`a_group_index`),
  KEY `a_type` (`a_type`)
) ENGINE=InnoDB AUTO_INCREMENT=87 DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_forum_permissions: ~70 rows (approximately)
DELETE FROM `t_forum_permissions`;
INSERT INTO `t_forum_permissions` (`a_index`, `a_forum_index`, `a_group_index`, `a_type`, `a_allowed`) VALUES
    (1, 3, 2, 0, 1),
    (2, 3, 3, 0, 1),
    (3, 3, 4, 0, 1),
    (4, 3, 6, 0, 1),
    (5, 6, 2, 0, 1),
    (6, 6, 3, 0, 1),
    (7, 6, 4, 0, 1),
    (8, 6, 6, 0, 1),
    (9, 4, 2, 0, 1),
    (10, 4, 2, 1, 1),
    (11, 4, 3, 0, 1),
    (12, 4, 3, 1, 1),
    (13, 4, 3, 3, 1),
    (14, 4, 4, 0, 1),
    (15, 4, 4, 1, 1),
    (16, 4, 4, 2, 1),
    (17, 4, 4, 3, 1),
    (18, 4, 4, 4, 1),
    (19, 4, 6, 0, 1),
    (20, 4, 6, 1, 1),
    (21, 4, 6, 2, 1),
    (22, 4, 6, 3, 1),
    (23, 4, 6, 4, 1),
    (24, 5, 2, 0, 1),
    (25, 5, 2, 1, 1),
    (26, 5, 3, 0, 1),
    (27, 5, 3, 1, 1),
    (28, 5, 3, 3, 1),
    (29, 5, 4, 0, 1),
    (30, 5, 4, 1, 1),
    (31, 5, 4, 2, 1),
    (32, 5, 4, 3, 1),
    (33, 5, 4, 4, 1),
    (34, 5, 6, 0, 1),
    (35, 5, 6, 1, 1),
    (36, 5, 6, 2, 1),
    (37, 5, 6, 3, 1),
    (38, 5, 6, 4, 1),
    (55, 9, 2, 0, 1),
    (56, 9, 2, 1, 1),
    (57, 9, 3, 0, 1),
    (58, 9, 3, 1, 1),
    (59, 9, 3, 2, 1),
    (60, 9, 3, 3, 1),
    (61, 9, 4, 0, 1),
    (62, 9, 4, 1, 1),
    (63, 9, 4, 2, 1),
    (64, 9, 4, 3, 1),
    (65, 9, 4, 4, 1),
    (66, 9, 6, 0, 1),
    (67, 9, 6, 1, 1),
    (68, 9, 6, 2, 1),
    (69, 9, 6, 3, 1),
    (70, 9, 6, 4, 1),
    (71, 7, 2, 0, 1),
    (72, 7, 2, 1, 1),
    (73, 7, 3, 0, 1),
    (74, 7, 3, 1, 1),
    (75, 7, 3, 2, 1),
    (76, 7, 3, 3, 1),
    (77, 7, 4, 0, 1),
    (78, 7, 4, 1, 1),
    (79, 7, 4, 2, 1),
    (80, 7, 4, 3, 1),
    (81, 7, 4, 4, 1),
    (82, 7, 6, 0, 1),
    (83, 7, 6, 1, 1),
    (84, 7, 6, 2, 1),
    (85, 7, 6, 3, 1),
    (86, 7, 6, 4, 1);

-- Dumping structure for table jvgforum.t_likes
CREATE TABLE IF NOT EXISTS `t_likes` (
  `a_index` int NOT NULL AUTO_INCREMENT,
  `a_post_index` int NOT NULL DEFAULT '-1',
  `a_member_index` int NOT NULL DEFAULT '-1',
  `a_timestamp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_index`),
  KEY `a_post_index` (`a_post_index`),
  KEY `a_user_index` (`a_member_index`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_likes: ~0 rows (approximately)
DELETE FROM `t_likes`;

-- Dumping structure for table jvgforum.t_members
CREATE TABLE IF NOT EXISTS `t_members` (
  `a_member_index` int NOT NULL AUTO_INCREMENT,
  `a_group_index` int NOT NULL DEFAULT '3',
  `a_username` varchar(20) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `a_password_hash` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `a_displayname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `a_email` varchar(100) NOT NULL DEFAULT '',
  `a_registration_date` int NOT NULL DEFAULT '0',
  `a_ip_address` varchar(50) NOT NULL DEFAULT '',
  `a_last_activity` int NOT NULL DEFAULT '0',
  `a_last_post` int NOT NULL DEFAULT '0',
  `a_posts` int NOT NULL DEFAULT '0',
  `a_avatar_url` varchar(200) NOT NULL DEFAULT '',
  `a_avatar_bgcolor` varchar(50) NOT NULL DEFAULT '',
  `a_last_mark_read` int NOT NULL DEFAULT '0',
  `a_is_moderator` tinyint NOT NULL DEFAULT '0' COMMENT 'Is a global moderator',
  `a_is_administrator` tinyint NOT NULL DEFAULT '0' COMMENT 'Is administrator of this forum',
  PRIMARY KEY (`a_member_index`) USING BTREE,
  KEY `a_username` (`a_username`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_members: ~1 rows (approximately)
DELETE FROM `t_members`;
INSERT INTO `t_members` (`a_member_index`, `a_group_index`, `a_username`, `a_password_hash`, `a_displayname`, `a_email`, `a_registration_date`, `a_ip_address`, `a_last_activity`, `a_last_post`, `a_posts`, `a_avatar_url`, `a_avatar_bgcolor`, `a_last_mark_read`, `a_is_moderator`, `a_is_administrator`) VALUES
    (1, 4, 'Administrator', '$2y$10$yYfZ1Bf3XRZjdVhUAwskruSUwv18DfM/6axRBxnWtbvtForncsR.W', 'Administrator', 'email@domain.com', 1615675486, '127.0.0.1', 1758636310, 1758636308, 262, '', '#3f8eca', 1730065494, 0, 1);

-- Dumping structure for table jvgforum.t_member_cookies
CREATE TABLE IF NOT EXISTS `t_member_cookies` (
  `a_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_expire_time` int NOT NULL DEFAULT '0',
  `a_member_index` int NOT NULL DEFAULT '-1',
  `a_key` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`a_index`),
  KEY `a_member_index` (`a_member_index`),
  KEY `a_key` (`a_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_member_cookies: ~0 rows (approximately)
DELETE FROM `t_member_cookies`;

-- Dumping structure for table jvgforum.t_member_groups
CREATE TABLE IF NOT EXISTS `t_member_groups` (
  `a_group_index` int NOT NULL,
  `a_name` varchar(50) NOT NULL DEFAULT '',
  `a_prefix` varchar(50) NOT NULL DEFAULT '',
  `a_suffix` varchar(50) NOT NULL DEFAULT '',
  `a_is_moderator` tinyint NOT NULL DEFAULT '0',
  `a_is_administrator` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_member_groups: ~5 rows (approximately)
DELETE FROM `t_member_groups`;
INSERT INTO `t_member_groups` (`a_group_index`, `a_name`, `a_prefix`, `a_suffix`, `a_is_moderator`, `a_is_administrator`) VALUES
    (2, 'Guests', '', '', 0, 0),
    (3, 'Members', '', '', 0, 0),
    (4, 'Administrators', '<span style=\'color:#f44336\'>', '</span>', 1, 1),
    (6, 'Moderators', '', '', 0, 0);

-- Dumping structure for table jvgforum.t_moderation_log
CREATE TABLE IF NOT EXISTS `t_moderation_log` (
  `a_index` int NOT NULL AUTO_INCREMENT,
  `a_moderator_index` int NOT NULL DEFAULT '0',
  `a_member_index` int NOT NULL DEFAULT '0',
  `a_forum_index` int NOT NULL DEFAULT '0',
  `a_topic_index` int NOT NULL DEFAULT '0',
  `a_post_index` int NOT NULL DEFAULT '0',
  `a_action` varchar(50) NOT NULL DEFAULT '',
  `a_timestamp` int NOT NULL DEFAULT '0',
  `a_ip` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_index`),
  KEY `a_member_index` (`a_member_index`),
  KEY `a_forum_index` (`a_forum_index`),
  KEY `a_topic_index` (`a_topic_index`),
  KEY `a_post_index` (`a_post_index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_moderation_log: ~0 rows (approximately)
DELETE FROM `t_moderation_log`;

-- Dumping structure for table jvgforum.t_posts
CREATE TABLE IF NOT EXISTS `t_posts` (
  `a_post_index` int NOT NULL AUTO_INCREMENT,
  `a_topic_index` int NOT NULL DEFAULT '0',
  `a_member_index` bigint unsigned NOT NULL DEFAULT '0',
  `a_ip_address` varchar(46) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `a_post_date` int DEFAULT NULL,
  `a_message` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `a_likes` int NOT NULL DEFAULT '0',
  `a_lastedit_time` int NOT NULL DEFAULT '0',
  `a_lastedit_member` int NOT NULL DEFAULT '-1',
  `a_is_hidden` int NOT NULL DEFAULT '0',
  `a_is_deleted` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_post_index`),
  KEY `a_member_index` (`a_member_index`),
  KEY `a_topic_index` (`a_topic_index`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table jvgforum.t_posts: ~8 rows (approximately)
DELETE FROM `t_posts`;
INSERT INTO `t_posts` (`a_post_index`, `a_topic_index`, `a_member_index`, `a_ip_address`, `a_post_date`, `a_message`, `a_likes`, `a_lastedit_time`, `a_lastedit_member`, `a_is_hidden`, `a_is_deleted`) VALUES
    (1, 1, 1, '127.0.0.1', 1758636308, '<p>Hello,</p><p></p><p>This is the first topic on this forum.</p>', 0, 0, -1, 0, 0);

-- Dumping structure for table jvgforum.t_registrations
CREATE TABLE IF NOT EXISTS `t_registrations` (
  `a_index` int NOT NULL AUTO_INCREMENT,
  `a_username` varchar(20) NOT NULL DEFAULT '',
  `a_password_hash` varchar(200) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `a_email` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL DEFAULT '',
  `a_ip` varchar(50) NOT NULL DEFAULT '',
  `a_code` varchar(10) NOT NULL DEFAULT '',
  `a_timestamp` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_registrations: ~0 rows (approximately)
DELETE FROM `t_registrations`;

-- Dumping structure for table jvgforum.t_topics
CREATE TABLE IF NOT EXISTS `t_topics` (
  `a_topic_index` int unsigned NOT NULL AUTO_INCREMENT,
  `a_forum_index` int unsigned NOT NULL,
  `a_is_pinned` tinyint NOT NULL DEFAULT '0',
  `a_is_closed` tinyint NOT NULL DEFAULT '0',
  `a_is_hidden` tinyint NOT NULL DEFAULT '0',
  `a_title` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `a_create_user` int NOT NULL DEFAULT '-1',
  `a_create_time` int NOT NULL DEFAULT '0',
  `a_posts` int NOT NULL DEFAULT '0',
  `a_views` int NOT NULL DEFAULT '0',
  `a_lastpost_user` int NOT NULL DEFAULT '-1',
  `a_lastpost_time` int NOT NULL DEFAULT '0',
  `a_lastpost_index` int NOT NULL DEFAULT '-1',
  PRIMARY KEY (`a_topic_index`) USING BTREE,
  KEY `a_forum_index` (`a_forum_index`),
  KEY `a_lastpost_time` (`a_lastpost_time`),
  KEY `a_lastpost_user` (`a_lastpost_user`),
  KEY `a_is_hidden` (`a_is_hidden`),
  KEY `idx_forum_hidden_lastpost` (`a_forum_index`,`a_is_hidden`,`a_lastpost_time`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_topics: ~0 rows (approximately)
DELETE FROM `t_topics`;
INSERT INTO `t_topics` (`a_topic_index`, `a_forum_index`, `a_is_pinned`, `a_is_closed`, `a_is_hidden`, `a_title`, `a_create_user`, `a_create_time`, `a_posts`, `a_views`, `a_lastpost_user`, `a_lastpost_time`, `a_lastpost_index`) VALUES
    (1, 4, 0, 0, 0, 'My First Topic', 1, 1758636308, 1, 0, 1, 1758636308, 1);

-- Dumping structure for table jvgforum.t_topic_visits
CREATE TABLE IF NOT EXISTS `t_topic_visits` (
  `a_member_index` int unsigned NOT NULL,
  `a_topic_index` int NOT NULL,
  `a_lastvisit` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`a_member_index`,`a_topic_index`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- Dumping data for table jvgforum.t_topic_visits: ~0 rows (approximately)
DELETE FROM `t_topic_visits`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
