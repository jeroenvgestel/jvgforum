<?php

    class TopicModel extends MysqlModel
    {
        private ?TopicInfo $topicInfo;

        /**
         * Returns a list of topics inside a specific forum
         *
         * @param integer $forumIndex
         * @param integer $page
         * @return false|Topic[]
         */
        public function getList(int $forumIndex, int $page = 1): false|Array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $user = User::Instance();
            
            $limit = Config::TOPICS_PER_PAGE;
            $offset = ($page - 1) * $limit;

            $query = sprintf("
                SELECT
                    t.a_topic_index,
                    t.a_title,
                    t.a_posts,
                    t.a_views,
                    t.a_is_pinned,
                    t.a_is_closed,
                    t.a_is_hidden,

                    sm.a_member_index as a_starter_index,
                    sm.a_displayname as a_starter_name,
                    sm.a_avatar_url as a_starter_avatar_url,
                    sm.a_avatar_bgcolor as a_starter_avatar_bgcolor,
                    t.a_create_time as a_starter_time,
                    sg.a_prefix as a_starter_prefix,
                    sg.a_suffix as a_starter_suffix,

                    lm.a_member_index as a_lastpost_index,
                    lm.a_displayname as a_lastpost_name,
                    lm.a_avatar_url as a_lastpost_avatar_url,
                    lm.a_avatar_bgcolor as a_lastpost_avatar_bgcolor,
                    t.a_lastpost_time as a_lastpost_time,
                    lg.a_prefix as a_lastpost_prefix,
                    lg.a_suffix as a_lastpost_suffix,

                    v.a_lastvisit as a_member_last_visit

                FROM
                    t_topics t
                        LEFT JOIN t_topic_visits v ON v.a_topic_index = t.a_topic_index AND v.a_member_index = :member_index,
                    t_members lm, t_member_groups lg,
                    t_members sm, t_member_groups sg

                WHERE t.a_forum_index = :forum_index
                AND sm.a_member_index = t.a_create_user
                AND sg.a_group_index = sm.a_group_index

                AND lm.a_member_index = t.a_lastpost_user
                AND lg.a_group_index = lm.a_group_index

                ORDER BY 
                    t.a_is_pinned DESC,
                    t.a_lastpost_time DESC
                    
                LIMIT %d
                OFFSET %d", 
                    $limit,
                    $offset
            );

                
            $result = $db->select($query, [
                ':forum_index' => $forumIndex,
                ':member_index' => $user->index,                
            ]);
            
            if(!$result)
                return false;

            /** @var Topic[] $topiclist */
            $topiclist = [];

            foreach($result as $r)
            {

                $lastViewTime = $user->lastMarkAllRead;
                if(isset($r['a_member_last_visit']) && $r['a_member_last_visit'] > $lastViewTime)
                    $lastViewTime = $r['a_member_last_visit'];

                $hasNewPosts = $r['a_lastpost_time'] > $lastViewTime;

                if($r['a_is_hidden'] && !Permissions::CanModerateForum($forumIndex))
                    continue;

                $topic = new Topic();
                $topic->index = $r['a_topic_index'];
                $topic->title = $r['a_title'];
                $topic->url = URL . 'topic/' . $r['a_topic_index'];
                $topic->posts = Utils::readableNumber($r['a_posts']);
                $topic->views = Utils::readableNumber($r['a_views']);
                $topic->isPinned = $r['a_is_pinned'];
                $topic->isClosed = $r['a_is_closed'];
                $topic->isHidden = $r['a_is_hidden'];
                $topic->hasNewPosts = $hasNewPosts;

                $topic->starter = new Poster();
                $topic->starter->setData('a_starter', $r);
                
                $topic->lastpost = new Poster();
                $topic->lastpost->setData('a_lastpost', $r);
                

                $topiclist[] = $topic;
            }

            return $topiclist;
        }


        /**
         * Returns a list of the most recent topics with new posts
         *
         * @param integer $page
         * @return false|Topic[]
         */
        public function getRecentList(int $page = 1): false|Array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $user = User::Instance();


            $limit = Config::TOPICS_PER_PAGE;
            $offset = ($page - 1) * $limit;

            $query = sprintf("
                SELECT
                    f.a_forum_index,
                    f.a_name as a_forum_name,
                    t.a_topic_index,
                    t.a_title,
                    t.a_posts,
                    t.a_views,
                    t.a_is_pinned,
                    t.a_is_closed,
                    t.a_is_hidden,

                    sm.a_member_index as a_starter_index,
                    sm.a_displayname as a_starter_name,
                    sm.a_avatar_url as a_starter_avatar_url,
                    sm.a_avatar_bgcolor as a_starter_avatar_bgcolor,
                    t.a_create_time as a_starter_time,
                    sg.a_prefix as a_starter_prefix,
                    sg.a_suffix as a_starter_suffix,

                    lm.a_member_index as a_lastpost_index,
                    lm.a_displayname as a_lastpost_name,
                    lm.a_avatar_url as a_lastpost_avatar_url,
                    lm.a_avatar_bgcolor as a_lastpost_avatar_bgcolor,
                    t.a_lastpost_time as a_lastpost_time,
                    lg.a_prefix as a_lastpost_prefix,
                    lg.a_suffix as a_lastpost_suffix,

                    v.a_lastvisit as a_member_last_visit


                FROM
                    t_topics t
                        LEFT JOIN t_topic_visits v ON v.a_topic_index = t.a_topic_index AND v.a_member_index = :member_index,
                    t_members lm, t_member_groups lg,
                    t_members sm, t_member_groups sg,
                    t_member_groups mg,
                    t_forums f,
                    t_forum_permissions p

                WHERE sm.a_member_index = t.a_create_user
                AND sg.a_group_index = sm.a_group_index

                AND lm.a_member_index = t.a_lastpost_user
                AND lg.a_group_index = lm.a_group_index

                AND f.a_forum_index = t.a_forum_index
                AND mg.a_group_index = :group_index
                AND p.a_group_index = mg.a_group_index
                AND p.a_forum_index = f.a_forum_index
                AND p.a_type = %d
                AND t.a_lastpost_time > :timestamp

                ORDER BY t.a_lastpost_time DESC
                    
                LIMIT %d
                OFFSET %d", 
                    Permissions::SEE_FORUM,
                    $limit,
                    $offset
            );

                
            $result = $db->select($query, [
                ':group_index' => $user->group->index,
                ':member_index' => $user->index,   
                ':timestamp' => time() - Config::RECENT_CONTENT_TIME             
            ]);

            if(!$result)
                return false;

            $topiclist = [];

            foreach($result as $r)
            {

                $lastViewTime = $user->lastMarkAllRead;
                if(isset($r['a_member_last_visit']) && $r['a_member_last_visit'] > $lastViewTime)
                    $lastViewTime = $r['a_member_last_visit'];

                $hasNewPosts = $r['a_lastpost_time'] > $lastViewTime;

                if($r['a_is_hidden'] && !Permissions::CanModerateForum($r['a_forum_index']))
                    continue;

                $topic = new Topic();
                $topic->index = $r['a_topic_index'];
                $topic->title = $r['a_title'];
                $topic->url = URL . 'topic/view/' . $r['a_topic_index'];
                $topic->forumIndex = $r['a_forum_index'];
                $topic->forumName = $r['a_forum_name'];
                $topic->forumUrl = URL . 'forum/view/' . $r['a_forum_index'];
                $topic->posts = Utils::readableNumber($r['a_posts']);
                $topic->views = Utils::readableNumber($r['a_views']);
                $topic->isPinned = $r['a_is_pinned'];
                $topic->isClosed = $r['a_is_closed'];
                $topic->isHidden = $r['a_is_hidden'];
                $topic->hasNewPosts = $hasNewPosts;
                
                $topic->starter = new Poster();
                $topic->starter->setData('a_starter', $r);
                
                $topic->lastpost = new Poster();
                $topic->lastpost->setData('a_lastpost', $r);

                $topiclist[] = $topic;
            }

            return $topiclist;
        }
        
        
        /**
         * Add topic to the database and return the new topicIndex
         * @param int $userIndex
         * @param int $forumIndex
         * @param string $title must be sanitized before
         * @return false|int false if failed, topicIndex when success
         */
        public function addTopic(int $userIndex, int $forumIndex, string $title): false|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $timestamp = time();

            $topicIndex = -1;
            $result = $db->insert("t_topics", [
                'a_forum_index' => $forumIndex,
                'a_title' => $title,
                'a_create_user' => $userIndex,
                'a_create_time' => $timestamp,
                'a_lastpost_user' => $userIndex,
                'a_lastpost_time' => $timestamp,
            ], $topicIndex);

            if($result === false)
                return false;


            return $topicIndex;
        }
        
        
        /**
         * Gets the topic info from the database
         * permissions are not checked
         * @param int $topicIndex
         * @return false|TopicInfo false if not found
         */
        public function getTopicInfo(int $topicIndex): false|TopicInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    t.a_topic_index,
                    t.a_title AS a_topic_title,
                    t.a_is_closed,
                    t.a_is_pinned,
                    t.a_is_hidden,

                    f.a_forum_index AS a_forum_index,
                    f.a_name AS a_forum_name,
                    c.a_forum_index AS a_category_index,
                    c.a_name AS a_category_name

                FROM
                    t_topics t,
                    t_forums f
                    LEFT JOIN t_forums c ON c.a_forum_index = f.a_parent_index
                    
                WHERE t.a_topic_index = :topic_index
                AND f.a_forum_index = t.a_forum_index", [
                    ':topic_index' => $topicIndex
                ]
            );

            if(!$r)
                return false;

            $topicInfo = new TopicInfo();
            $topicInfo->index = $r['a_topic_index'];
            $topicInfo->title = $r['a_topic_title'];
            $topicInfo->url = URL . 'topic/view/' . $r['a_topic_index'];
            $topicInfo->isClosed = $r['a_is_closed'];
            $topicInfo->isPinned = $r['a_is_pinned'];
            $topicInfo->isHidden = $r['a_is_hidden'];
            $topicInfo->canPost = Permissions::CanReplyTopic($r['a_forum_index'], $r['a_is_closed'], $r['a_is_hidden']);

            $topicInfo->forum = new TopicInfoForum();
            $topicInfo->forum->index = $r['a_forum_index'];
            $topicInfo->forum->name = $r['a_forum_name'];
            $topicInfo->forum->url = URL . 'forum/view/' . $r['a_forum_index'];
            $topicInfo->forum->isModerator = Permissions::CanModerateForum($r['a_forum_index']);

            $topicInfo->category = new TopicInfoCategory();
            $topicInfo->category->index = $r['a_category_index'];
            $topicInfo->category->name = $r['a_category_name'];
            $topicInfo->category->url = URL . 'index/cat/' . $r['a_category_index'];
            
            $this->topicInfo = $topicInfo;

            return $topicInfo;
        }
        
        
        /**
         * Gets the amount of posts in a topic, if user is a moderator
         * the hidden topics are also included
         * @param int $topic_index
         * @return int
         */
        public function getPostCount(int $topic_index): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;

            $r = $db->selectOne("
                SELECT
                    count(*) as a_count

                FROM
                    t_posts p
                
                WHERE p.a_topic_index = :topic_index
                AND (p.a_is_hidden = 0 OR (p.a_is_hidden = 1 AND :ismoderator))
                AND p.a_is_deleted = 0", [
                    ':topic_index' => $topic_index,
                    ':ismoderator' => $this->topicInfo->forum->isModerator
                ]
            );

            return $r['a_count'];
        }
        
        
        /**
         * Checks if a topic is closed or hidden and if there are enough
         * permissions on the forum level
         * @param int $topic_index
         * @return bool
         */
        public function userCanPostInTopic(int $topic_index): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    t.a_topic_index,
                    t.a_is_closed,
                    t.a_is_hidden,
                    t.a_forum_index

                FROM
                    t_topics t

                WHERE t.a_topic_index = :topic_index", [
                    ':topic_index' => $topic_index
                ]
            );

            if(!$r)
                return false;

            if(!Permissions::CanReplyTopic($r['a_forum_index'], $r['a_is_closed'], $r['a_is_hidden']))
                return false;

            return true;

        }
        
        
        /**
         * Gets the required information to be able to edit a topic
         * @param int $topic_index
         * @return false|EditTopicInfo
         */
        public function getInfoForEdit(int $topic_index): false|EditTopicInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    t.a_topic_index,
                    t.a_title,
                    t.a_is_pinned,
                    t.a_is_closed,
                    p.a_post_index,
                    p.a_message

                FROM 
                    t_topics t,
                    t_posts p
                        
                WHERE t.a_topic_index = :topic_index
                AND p.a_topic_index = t.a_topic_index
                ORDER BY p.a_post_index 
                LIMIT 1", [
                    ':topic_index' => $topic_index
                ]
            );

            if(!$r)
                return false;

            
            $editTopicInfo = new EditTopicInfo();
            $editTopicInfo->topicIndex = $r['a_topic_index'];
            $editTopicInfo->postIndex = $r['a_post_index'];
            $editTopicInfo->isPinned = $r['a_is_pinned'];
            $editTopicInfo->isClosed = $r['a_is_closed'];
            $editTopicInfo->title = $r['a_title'];
            $editTopicInfo->message = $r['a_message'];
            
            return $editTopicInfo;
        }

        
        /**
         * Updates the title of a topic
         *
         * @param integer $topicIndex
         * @param string $title Must be stripped of tags already
         * @return boolean
         */
        public function updateTitle(int $topicIndex, string $title): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            return $db->update("UPDATE t_topics SET a_title = :title WHERE a_topic_index = :topic_index", [
                ':title' => $title,
                ':topic_index' => $topicIndex
            ]);

        }
        
        
        /**
         * Verifies if a user can edit the specified topic and post
         * @param int $topic_index
         * @param int $post_index
         * @return bool
         */
        public function userCanEditTopicandPost(int $topic_index, int $post_index): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    t.a_forum_index,
                    t.a_create_user,
                    p.a_post_index

                FROM
                    t_topics t,
                    t_posts p

                WHERE t.a_topic_index = :topic_index
                AND p.a_topic_index = t.a_topic_index
                AND p.a_post_index = :post_index", [
                    ':topic_index' => $topic_index,
                    ':post_index' => $post_index
                ]
            );

            if(!$r)
                return false;

            return Permissions::CanModerateForum($r['a_forum_index']);

        }
        
        
        /**
         * Gets the list of forums to show in the move topic page
         * @return false|MoveTopicForumInfo[]
         */
        public function getForumList(): false|Array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $result = $db->select("
                SELECT
                    a_forum_index,
                    a_parent_index,
                    a_type,
                    a_name
                FROM
                    t_forums
                ORDER BY
                    a_type,
                    a_parent_index,
                    a_sort"
            );

            if(!$result)
                return false;
            
            
            /** @var MoveTopicForumInfo[] $allforums */
            $allforums = [];
            foreach($result as $r)
            {
                $moveTopicForumInfo = new MoveTopicForumInfo();
                $moveTopicForumInfo->forumIndex = $r['a_forum_index'];
                $moveTopicForumInfo->type = $r['a_type'];
                $moveTopicForumInfo->parentIndex = $r['a_parent_index'];
                $moveTopicForumInfo->name = $r['a_name'];

                if(!Permissions::CanSeeForum($moveTopicForumInfo->forumIndex))
                    continue;

                $allforums[$moveTopicForumInfo->forumIndex] = $moveTopicForumInfo;
            }
            
            
            /** @var MoveTopicForumInfo[] $data */
            $data = [];
            foreach($allforums as &$forum)
            {
                if($forum->parentIndex == -1)
                {
                    $data[] = &$forum;
                    continue;
                }

                $parent = &$allforums[$forum->parentIndex];
                $parent->children[$forum->forumIndex] = &$forum;
            }

            return $data;            
        }
        
        
        /**
         * Moves a topic to a different forum
         * @param int $topicIndex the topic to move
         * @param int $forumIndex the forum to move to
         * @return bool
         */
        public function moveTopic(int $topicIndex, int $forumIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            return $db->update("UPDATE t_topics SET a_forum_index = :forum_index WHERE a_topic_index = :topic_index", [
                ':forum_index' => $forumIndex,
                ':topic_index' => $topicIndex
            ]);

        }
        
        
        /**
         * Update the topic state to hidden or not
         * @param int $topicIndex
         * @param bool $hide
         * @return bool
         */
        public function setHide(int $topicIndex, bool $hide): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            return $db->update("UPDATE t_topics SET a_is_hidden = :hide WHERE a_topic_index = :topic_index", [
                ':topic_index' => $topicIndex,
                ':hide' => $hide ? 1 : 0
            ]);
        }
        
        
        /**
         * Update the topic state to closed or not
         * @param int $topicIndex
         * @param bool $closed
         * @return bool
         */
        public function setClosed(int $topicIndex, bool $closed): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            return $db->update("UPDATE t_topics SET a_is_closed = :closed WHERE a_topic_index = :topic_index", [
                ':topic_index' => $topicIndex,
                ':closed' => $closed ? 1 : 0
            ]);
        }
        
        
        /**
         * Update the topic state to pinned or not
         * @param int $topicIndex
         * @param bool $pinned
         * @return bool
         */
        public function setPinned(int $topicIndex, bool $pinned): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            return $db->update("UPDATE t_topics SET a_is_pinned = :pinned WHERE a_topic_index = :topic_index", [
                ':topic_index' => $topicIndex,
                ':pinned' => $pinned ? 1 : 0
            ]);
        }
        
        
        public function registerTopicVisit(int $topicIndex, int $userIndex): void
        {
            if(!$db = $this->getDatabaseConnection())
                return;
            
            $db->update("
                INSERT INTO t_topic_visits (
                    a_member_index,
                    a_topic_index,
                    a_lastvisit
                )
                VALUES(
                    :member_index,
                    :topic_index,
                    :timestamp
                )
                
                ON DUPLICATE KEY
                    UPDATE a_lastvisit = :timestamp",
                [
                    ':member_index' => $userIndex,
                    ':topic_index' => $topicIndex,
                    ':timestamp' => time()
                ]
            );
            
        }
        
        
        /**
         * @param int $topicIndex
         * @return bool
         */
        public function recountPostsInTopic(int $topicIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->update("
                UPDATE t_topics t
                SET
                    t.a_posts = (
                        SELECT COUNT(*)
                        FROM
                            t_posts p
                        WHERE p.a_topic_index = t.a_topic_index
                        AND p.a_is_hidden = 0
                        AND p.a_is_deleted = 0
                    )
                WHERE t.a_topic_index = :topic_index", [
                    ':topic_index' => $topicIndex
                ]
            );
        }
        
        
        
        
        /**
         * Update the last post setting in a topic
         * @param int $topicIndex
         * @return bool
         */
        public function updateTopicLastPost(int $topicIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->update("
                UPDATE t_topics t
                JOIN (
                    SELECT
                        a_topic_index,
                        MAX(a_post_index) AS a_lastpost_index,
                        a_post_date AS a_lastpost_time,
                        a_member_index AS a_lastpost_user

                    FROM t_posts
                    WHERE a_is_hidden = 0 AND a_is_deleted = 0
                    GROUP BY a_topic_index
                ) latest_post
                ON t.a_topic_index = latest_post.a_topic_index
                SET
                    t.a_lastpost_index = latest_post.a_lastpost_index,
                    t.a_lastpost_time = latest_post.a_lastpost_time,
                    t.a_lastpost_user = latest_post.a_lastpost_user
                WHERE t.a_topic_index = :topic_index", [
                    ':topic_index' => $topicIndex
                ]
            );
        }
    }