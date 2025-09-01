<?php
    
    class LoggingModel extends MysqlModel
    {
        
        /**
         * Adds a log to the moderation log database
         * @param string $action
         * @param int $forumIndex
         * @param int $topicIndex
         * @param int $postIndex
         * @param int $memberIndex
         * @return bool
         */
        public function addModerationLog(string $action, int $forumIndex, int $topicIndex, int $postIndex, int $memberIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->insert('t_moderation_log', [
                'a_moderator_index' => User::Instance()->index,
                'a_member_index' => $memberIndex,
                'a_forum_index' => $forumIndex,
                'a_topic_index' => $topicIndex,
                'a_post_index' => $postIndex,
                'a_action' => $action,
                'a_timestamp' => time(),
                'a_ip' => $_SERVER['REMOTE_ADDR']
            ]);
        }
    }