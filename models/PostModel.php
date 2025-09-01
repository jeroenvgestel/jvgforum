<?php

    class PostModel extends MysqlModel
    {
        
        /**
         * Gets all the information about a post, including the message itself
         * @param int $postIndex
         * @return false|PostInfo
         */
        public function getPostInfo(int $postIndex): false|PostInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    p.a_post_index,
                    p.a_message,
                    p.a_member_index,
                    p.a_is_deleted,
                    p.a_is_hidden,
                    p.a_post_date,
                    t.a_topic_index,
                    t.a_title AS a_topic_title,
                    t.a_is_closed,
                    f.a_forum_index AS a_forum_index,
                    f.a_name AS a_forum_name,
                    c.a_forum_index AS a_category_index,
                    c.a_name AS a_category_name

                FROM
                    t_posts p,
                    t_topics t,
                    t_forums f
                    LEFT JOIN t_forums c ON c.a_forum_index = f.a_parent_index
                    
                WHERE p.a_post_index = :post_index
                AND t.a_topic_index = p.a_topic_index
                AND f.a_forum_index = t.a_forum_index", [
                    ':post_index' => $postIndex
                ]
            );

            if(!$r)
                return false;
            
            $canEditPost = Permissions::CanEditPost($r['a_forum_index'], $r['a_member_index'], $r['a_is_deleted'], $r['a_is_hidden'], $r['a_is_closed'], $r['a_post_date']);
            
            $postInfo = new PostInfo();
            $postInfo->index = $r['a_post_index'];
            $postInfo->message = $r['a_message'];
            $postInfo->userIndex = $r['a_member_index'];
            $postInfo->canEdit = $canEditPost;
            
            $postInfo->topic = new PostInfoTopic();
            $postInfo->topic->index = $r['a_topic_index'];
            $postInfo->topic->title = $r['a_topic_title'];
            $postInfo->topic->isClosed = $r['a_is_closed'];
            $postInfo->topic->url = URL . 'topic/' . $r['a_topic_index'];
            $postInfo->topic->canPost = Permissions::CanPostTopics($r['a_forum_index']);
            
            $postInfo->forum = new PostInfoForum();
            $postInfo->forum->index = $r['a_forum_index'];
            $postInfo->forum->name = $r['a_forum_name'];
            $postInfo->forum->url = URL . 'forum/' . $r['a_forum_index'];
            
            $postInfo->category = new PostInfoCategory();
            $postInfo->category->index = $r['a_category_index'];
            $postInfo->category->name = $r['a_category_name'];
            $postInfo->category->url = URL . 'category/' . $r['a_category_index'];
            
            return $postInfo;
        }
        
        
        /**
         * @param int $postIndex
         * @return false|PostInfoSmall
         */
        public function getPostInfoSmall(int $postIndex): false|PostInfoSmall
        {
            if(!Utils::isNumeric($postIndex))
                return false;

            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    p.a_post_index,
                    p.a_is_hidden,
                    p.a_member_index,
                    t.a_topic_index,
                    t.a_is_closed,
                    f.a_forum_index

                FROM
                    t_posts p,
                    t_topics t,
                    t_forums f
                    
                WHERE p.a_post_index = :post_index
                AND t.a_topic_index = p.a_topic_index
                AND f.a_forum_index = t.a_forum_index", [
                    ':post_index' => $postIndex,
                ]
            );

            if(!$r)
                return false;

            $postInfoSmall = new PostInfoSmall();
            $postInfoSmall->postIndex = $r['a_post_index'];
            $postInfoSmall->isHidden = $r['a_is_hidden'];
            $postInfoSmall->topicIndex = $r['a_topic_index'];
            $postInfoSmall->isTopicClosed = $r['a_is_closed'];
            $postInfoSmall->forumIndex = $r['a_forum_index'];
            $postInfoSmall->memberIndex = $r['a_member_index'];
            
            return $postInfoSmall;
        }
        
        
        /**
         * Checks if this user already liked a post, if so, remove it, else add it
         * @param int $postIndex
         * @param int $userIndex
         * @return bool
         */
        public function toggleLike(int $postIndex, int $userIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("SELECT count(*) as a_num FROM t_likes WHERE a_post_index = :post_index AND a_member_index = :user_index", [
                ':post_index' => $postIndex,
                ':user_index' => $userIndex
            ]);
            
            if($r['a_num'] == 0) // Add a like
            {
                $db->insert('t_likes', [
                    'a_post_index' => $postIndex,
                    'a_member_index' => $userIndex,
                    'a_timestamp' => time()
                ]);
            }
            else // Remove the like
            {
                $db->update("DELETE FROM t_likes WHERE a_post_index = :post_index AND a_member_index = :user_index", [
                    ':post_index' => $postIndex,
                    ':user_index' => $userIndex
                ]);
            }
            
            return true;
        }
        
        
        /**
         * Gets a list of all the likes of a post
         * @param int $postIndex
         * @return Poster[]
         */
        public function getLikes(int $postIndex): array
        {
            if(!$db = $this->getDatabaseConnection())
                return [];

            $result = $db->select("
                SELECT
                    l.a_post_index,
                    m.a_member_index,
                    m.a_displayname,
                    g.a_prefix,
                    g.a_suffix
                    
                FROM
                    t_likes l,
                    t_members m,
                    t_member_groups g
                    
                WHERE l.a_post_index = :post_index
                AND m.a_member_index = l.a_member_index
                AND g.a_group_index = m.a_group_index", [
                    ':post_index' => $postIndex
                ]
            );
            
            /** @var Poster[] $likesData */
            $likesData = [];
            
            if($result)
            {
                foreach($result as $r)
                {
                    $like = new Poster();
                    $like->url = URL . 'usercp/view/' . $r['a_member_index'];
                    $like->name = $r['a_displayname'];
                    $like->prefix = $r['a_prefix'];
                    $like->suffix = $r['a_suffix'];
                    
                    $likesData[] = $like;
                }
            }
            
            return $likesData;
        }
        
        
        /**
         * Updates a post message in the database
         * @param int $postIndex
         * @param int $userIndex
         * @param string $sanitizedMessage
         * @return bool
         */
        public function updatePost(int $postIndex, int $userIndex, string $sanitizedMessage): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->update("
                UPDATE t_posts
                SET
                    a_message = :message,
                    a_lastedit_time = :timestamp,
                    a_lastedit_member = :member_index
                WHERE a_post_index = :post_index", [
                    ':message' => $sanitizedMessage,
                    ':timestamp' => time(),
                    ':member_index' => $userIndex,
                    ':post_index' => $postIndex
                ]
            );
            
        }
        
        
        /**
         * Returns at which page in a topic a post is in
         * @param int $postIndex
         * @param int $topicIndex
         * @return int
         */
        public function getPageInTopic(int $postIndex, int $topicIndex): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 1;

            $r = $db->selectOne("
                SELECT CEIL(COUNT(*) / :post_per_page) as a_page
                FROM t_posts
                WHERE a_topic_index = :topic_index
                AND a_post_index <= :post_index", [
                    ':post_per_page' => Config::POSTS_PER_PAGE,
                    ':topic_index' => $topicIndex,
                    ':post_index' => $postIndex
                ]
            );

            if(!$r)
                return 1;

            return $r['a_page'];
        }
        
        
        /**
         * Set the hide state of a post
         * @param int $postIndex
         * @param bool $hide
         * @return bool
         */
        public function setHide(int $postIndex, bool $hide): bool
        {
            if(!$db = $this->getDatabaseConnection())
            {
                return false;
            }

            return $db->update("
                UPDATE
                    t_posts
                SET
                    a_is_hidden = :hidden
                WHERE a_post_index = :post_index", [
                    ':hidden' => $hide ? 1 : 0,
                    ':post_index' => $postIndex
                ]
            );

        }
        
        
        /**
         * Add a reply to a topic and update the lastpost in forum and topic
         * @param int $userIndex
         * @param int $topicIndex
         * @param string $sanitizedMessage
         * @return bool|int New post index
         */
        public function addReplyToTopic(int $userIndex, int $topicIndex, string $sanitizedMessage): bool|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $timestamp = time();

            $postIndex = -1;
            $result = $db->insert('t_posts', [
                'a_topic_index' => $topicIndex,
                'a_member_index' => $userIndex,
                'a_ip_address' => $_SERVER['REMOTE_ADDR'],
                'a_post_date' => $timestamp,
                'a_message' => $sanitizedMessage
            ], $postIndex);

            if($result === false)
                return false;

            $db->update("
                UPDATE 
                    t_topics t, 
                    t_forums f

                SET
                    t.a_posts = t.a_posts + 1,
                    t.a_lastpost_user = :user_index,
                    t.a_lastpost_time = :timestamp,
                    t.a_lastpost_index = :post_index,

                    f.a_post_count = f.a_post_count + 1,
                    f.a_lastpost_user = :user_index,
                    f.a_lastpost_topic = t.a_topic_index,
                    f.a_lastpost_time = :timestamp

                WHERE t.a_topic_index = :topic_index
                AND f.a_forum_index = t.a_forum_index", [
                    ':topic_index' => $topicIndex,
                    ':post_index' => $postIndex,
                    ':user_index' => $userIndex,
                    ':timestamp' => $timestamp
                ]
            );

            return $postIndex;
        }
        

        /**
         * Gets the list of posts for a specific page in a topic
         *
         * @param integer $topicIndex
         * @param integer $page
         * @param TopicInfo $topicInfo
         * @return false|Post[]
         */
        public function getList(int $topicIndex, int $page, TopicInfo $topicInfo): false|Array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $limit = Config::POSTS_PER_PAGE;
            $offset = ($page - 1) * $limit;                

            $query = sprintf("
                SELECT
                    p.a_post_index,
                    p.a_ip_address,
                    p.a_post_date,
                    p.a_message,
                    p.a_is_hidden,
                    p.a_is_deleted,
                    
                    m.a_member_index,
                    m.a_displayname,
                    m.a_avatar_url,
                    m.a_avatar_bgcolor,
                    m.a_posts as a_user_postcount,
                    
                    g.a_prefix,
                    g.a_suffix,
                    g.a_name as a_group_name,

                    p.a_lastedit_member,
                    me.a_displayname as a_lastedit_name,
                    p.a_lastedit_time,     
                    ge.a_prefix as a_lastedit_prefix,
                    ge.a_suffix as a_lastedit_suffix

                FROM
                    t_posts p
                        LEFT JOIN t_members me ON me.a_member_index = p.a_lastedit_member
                        LEFT JOIN t_member_groups ge ON ge.a_group_index = me.a_group_index,
                    t_members m,
                    t_member_groups g
                
                WHERE p.a_topic_index = :topic_index
                AND (p.a_is_hidden = 0 OR (p.a_is_hidden = 1 AND :ismoderator))
                AND p.a_is_deleted = 0
                AND p.a_member_index = m.a_member_index
                AND g.a_group_index = m.a_group_index
                ORDER BY p.a_post_date
                
                LIMIT %d
                OFFSET %d",
                $limit,
                $offset
            );

            $result = $db->select($query, [
                ':topic_index' => $topicIndex,
                ':ismoderator' => $topicInfo->forum->isModerator,
            ]);

            if(!$result)
                return false;

            $postlist = [];
            foreach($result as $r)
            {
                $canEdit = Permissions::CanEditPost($topicInfo->forum->index, $r['a_member_index'], $r['a_is_deleted'], $r['a_is_hidden'], $topicInfo->isClosed, $r['a_post_date']);
                $canHide = Permissions::CanHidePost($topicInfo->forum->index, $r['a_member_index'], $r['a_is_deleted'], $r['a_is_hidden'], $topicInfo->isClosed);

                $post = new Post();
                $post->index = $r['a_post_index'];
                $post->ipAddress = $r['a_ip_address'];
                $post->date = Utils::processTimestamp($r['a_post_date']);
                $post->message = $r['a_message'];
                $post->isHidden = $r['a_is_hidden'];
                $post->canDelete = $canHide ? 1 : 0;
                $post->deleteUrl = URL . 'post/delete/' . $r['a_post_index'];
                $post->canEdit = $canEdit ? 1 : 0;
                $post->editUrl = URL . 'post/edit/' . $r['a_post_index'];

                $post->user = new Poster();
                $post->user->index = $r['a_member_index'];
                $post->user->name = $r['a_displayname'];
                $post->user->url = URL . 'usercp/view/' . $r['a_member_index'];
                $post->user->avatarUrl = $r['a_avatar_url'];
                $post->user->avatarBgColor = 'style="fill: ' . $r['a_avatar_bgcolor'] . '" ';
                $post->user->groupName = $r['a_group_name'];
                $post->user->prefix = $r['a_prefix'];
                $post->user->suffix = $r['a_suffix'];
                $post->user->postCount = $r['a_user_postcount'];
                
                $post->lastEdit = new Poster();
                $post->lastEdit->url = URL . 'usercp/view/' . $r['a_lastedit_member'];
                $post->lastEdit->name = $r['a_lastedit_name'] ?? '';
                $post->lastEdit->prefix = $r['a_lastedit_prefix'] ?? '';
                $post->lastEdit->suffix = $r['a_lastedit_suffix'] ?? '';
                $post->lastEdit->date = $r['a_lastedit_time'] > 0 ? Utils::processTimestamp($r['a_lastedit_time']) : ''; 

                $postlist[$post->index] = $post;
            }

            if(count($postlist) > 0)
            {
                $postIndices = array_keys($postlist);
                $query = sprintf("
                    SELECT
                        l.a_post_index,	
                        m.a_member_index,
                        m.a_displayname,
                        g.a_prefix,
                        g.a_suffix
                        
                        
                    FROM 
                        t_likes l,
                        t_members m,
                        t_member_groups g
                        
                    WHERE l.a_post_index IN (%s)
                    AND m.a_member_index = l.a_member_index
                    AND g.a_group_index = m.a_group_index", 
                    implode(', ', $postIndices)
                );

                $result = $db->select($query);
                if($result)
                {
                    foreach($result as $r)
                    {
                        $like = new Poster();
                        $like->url = URL . 'usercp/view/' . $r['a_member_index'];
                        $like->name = $r['a_displayname'];
                        $like->prefix = $r['a_prefix'];
                        $like->suffix = $r['a_suffix'];

                        $postIndex = $r['a_post_index'];

                        if(isset($postlist[$postIndex]))
                            $postlist[$postIndex]->likes[] = $like;
                    }
                }
            }        


            return $postlist;

        }
        
        
        /**
         * @param int $userIndex
         * @return void
         */
        public function increaseUserPostCount(int $userIndex): void
        {
            if(!$db = $this->getDatabaseConnection())
                return;
            
            $db->update("
                UPDATE t_members
                SET
                    a_posts = a_posts + 1,
                    a_last_post = :timestamp
                WHERE a_member_index = :member_index",
                [
                    ':timestamp' => time(),
                    ':member_index' => $userIndex
                ]
            );
        }
        
    }