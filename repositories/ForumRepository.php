<?php

    const FORUM_TYPE_CAT = 0;
    //const FORUM_TYPE_FORUM = 1;

    class ForumRepository extends MysqlRepository
    {

        /**
         * Get forum index, name, type, desc, parent from Database
         * TODO : Can come from forum cache
         *
         * @param integer $forumIndex
         * @return false|Forum false if the forum does not exist
         */
        public function getInfo(int $forumIndex): false|Forum
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                f.a_forum_index as a_forum_index,
                f.a_name as a_forum_name,
                f.a_type as a_forum_type,
                f.a_desc as a_forum_desc,
                c.a_forum_index as a_parent_index,
                c.a_name as a_parent_name,
                c.a_type as a_parent_type
                FROM
                    t_forums f
                    LEFT JOIN t_forums c ON c.a_forum_index = f.a_parent_index
                WHERE f.a_forum_index = :forum_index", [
                    ':forum_index' => $forumIndex
                ]
            );

            if($r === false)
                return false;

            $forum = new Forum();
            $forum->index = $r['a_forum_index'];
            $forum->type = $r['a_forum_type'];
            $forum->name = $r['a_forum_name'];
            $forum->desc = $r['a_forum_desc'];
            if(!is_null($r['a_parent_index']))
            {
                $forum->parent = new Forum();
                $forum->parent->index = $r['a_parent_index'];
                $forum->parent->type = $r['a_parent_type'];
                $forum->parent->name = $r['a_parent_name'];
            }

            return $forum;
        }


         /**
         * Count the topics in a specific forum
         * TODO: Add topic count in getInfo() ?
         *
         * @param int $forum_index
         * @return int number of topics, includes hidden topics
         */
        public function getTopicCount(int $forum_index): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;

            $r = $db->selectOne("
                SELECT
                    count(*) as a_count
                FROM
                    t_topics t

                WHERE t.a_forum_index = :forum_index", [
                    ':forum_index' => $forum_index
                ]
            );

            return $r['a_count'];
        }


        /**
         * Count the topics that have a post in the last xx time defined in the Config
         *
         * @return integer Amount of topics
         */
        public function getRecentTopicCount(): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;

            $r = $db->selectOne("                
                SELECT
                    COUNT(*) as a_count
                		
                FROM
                    t_topics t,
                    t_member_groups mg,
                    t_forums f,
                    t_forum_permissions p
					
				
                WHERE f.a_forum_index = t.a_forum_index
                AND mg.a_group_index = 2
                AND p.a_group_index = mg.a_group_index
                AND p.a_forum_index = f.a_forum_index
                AND p.a_type = :permission
                AND t.a_lastpost_time > :timestamp", [
                    ':timestamp' => time() - Config::RECENT_CONTENT_TIME,
                    ':permission' => Permissions::SEE_FORUM
                ]
            );

            return $r['a_count'];
        }
        
        
        /**
         * Updates the database to set the last read point to the current time
         * @param int $userIndex
         * @return bool
         */
        public function markAllForumsRead(int $userIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $db->update("UPDATE t_members SET a_last_mark_read = :timestamp WHERE a_member_index = :member_index", [
                ':timestamp' => time(),
                ':member_index' => $userIndex
            ]);
            
            $db->update("DELETE FROM t_topic_visits WHERE a_member_index = :member_index", [
                ':member_index' => $userIndex
            ]);
            
            return true;
        }
        
        
        /**
         * Update the lastpost settings for a specific forum
         * @param int $forumIndex
         * @return bool
         */
        public function updateForumLastPost(int $forumIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $affectedRows = 0;
            
            $db->update(/*sql*/ "
                UPDATE t_forums f
                LEFT JOIN (
                    SELECT
                        t.a_forum_index,
                        t.a_topic_index AS a_lastpost_topic,
                        t.a_lastpost_user,
                        t.a_lastpost_time
                    FROM t_topics t
                    JOIN (
                        SELECT a_forum_index, MAX(a_lastpost_time) AS max_time
                        FROM t_topics
                        WHERE a_is_hidden = 0
                        GROUP BY a_forum_index
                    ) latest_time
                    ON t.a_forum_index = latest_time.a_forum_index
                    AND t.a_lastpost_time = latest_time.max_time
                    WHERE t.a_is_hidden = 0
                ) latest_post
                ON f.a_forum_index = latest_post.a_forum_index
                SET
                    f.a_lastpost_user = COALESCE(latest_post.a_lastpost_user, -1),
                    f.a_lastpost_topic = COALESCE(latest_post.a_lastpost_topic, -1),
                    f.a_lastpost_time = COALESCE(latest_post.a_lastpost_time, 0)
                    
                WHERE f.a_forum_index = :forum_index;", [
                ':forum_index' => $forumIndex
            ], $affectedRows
            );
            
            return $affectedRows != 0;
        }
        
        
        
        
        /**
         * Sets how many topics there are in a forum
         * @param int $forumIndex
         * @return bool
         */
        public function RecountTopicsInForum(int $forumIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->update("
                UPDATE t_forums f
                SET
                    f.a_topic_count = (
                        SELECT COUNT(*)
                        FROM
                            t_topics t
                        WHERE t.a_forum_index = f.a_forum_index
                        AND t.a_is_hidden = 0
                    )
                WHERE f.a_forum_index = :forum_index", [
                    ':forum_index' => $forumIndex
                ]
            );
        }
    }