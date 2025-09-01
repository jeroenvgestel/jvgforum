<?php
    
    class ConversationModel extends MysqlModel
    {
        /**
         * Reads the amount of conversations the user is in
         * @param int $userIndex
         * @return int
         */
        public function getConversationCount(int $userIndex): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;
            
            $r = $db->selectOne("
                SELECT
                    count(*) as a_count
                FROM
                    t_conversation_members pm,
                    t_conversations pt
                    
                WHERE pm.a_member_index = :member_index
                AND pt.a_conversation_index = pm.a_conversation_index", [
                    ':member_index' => $userIndex
                ]
            );
            
            return $r['a_count'];
        }
        
        
        /**
         * Reads the list of conversation for a certain page from the database
         * @param int $userIndex
         * @param int $page (default 1)
         * @return false|Conversation[]
         */
        public function getConversationList(int $userIndex, int $page): false|array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $limit = Config::TOPICS_PER_PAGE;
            $offset = ($page - 1) * $limit;
            
            $query = sprintf("
                SELECT
                    pt.a_conversation_index,
                    pt.a_title,
                    pt.a_posts,
                    
                    sm.a_member_index as a_starter_index,
                    sm.a_displayname as a_starter_name,
                    sm.a_avatar_url as a_starter_avatar_url,
                    sm.a_avatar_bgcolor as a_starter_avatar_bgcolor,
                    pt.a_create_time as a_starter_time,
                    sg.a_prefix as a_starter_prefix,
                    sg.a_suffix as a_starter_suffix,

                    lm.a_member_index as a_lastpost_index,
                    lm.a_displayname as a_lastpost_name,
                    lm.a_avatar_url as a_lastpost_avatar_url,
                    lm.a_avatar_bgcolor as a_lastpost_avatar_bgcolor,
                    pt.a_lastpost_time as a_lastpost_time,
                    lg.a_prefix as a_lastpost_prefix,
                    lg.a_suffix as a_lastpost_suffix,

                    pm.a_lastread AS a_member_last_visit
                    
                FROM
                    t_conversation_members pm,
                    t_conversations pt,
                    t_members lm, t_member_groups lg,
                    t_members sm, t_member_groups sg
                    
                WHERE pm.a_member_index = :member_index
                AND pt.a_conversation_index = pm.a_conversation_index

                AND sm.a_member_index = pt.a_create_user
                AND sg.a_group_index = sm.a_group_index

                AND lm.a_member_index = pt.a_lastpost_user
                AND lg.a_group_index = lm.a_group_index

                ORDER BY
                    pt.a_lastpost_time DESC
                    
                LIMIT %d
                OFFSET %d",
                $limit,
                $offset
            );
            
            
            $result = $db->select($query, [
                ':member_index' => $userIndex,
            ]);
            
            if(!$result)
                return false;
            
            /** @var Conversation[] $conversationList */
            $conversationList = [];
            
            foreach($result as $r)
            {
                $conversationInfo = new Conversation();
                $conversationInfo->index = $r['a_conversation_index'];
                $conversationInfo->title = $r['a_title'];
                $conversationInfo->url = URL . 'conversation/view/' . $r['a_conversation_index'];
                $conversationInfo->posts = Utils::readableNumber($r['a_posts']);
                $conversationInfo->memberLastVisit = $r['a_member_last_visit'];
                
                $conversationInfo->startPoster = new Poster();
                $conversationInfo->startPoster->setData('a_starter', $r);
                
                $conversationInfo->lastPoster = new Poster();
                $conversationInfo->lastPoster->setData('a_lastpost', $r);
                
                $conversationList[] = $conversationInfo;
            }
            
            return $conversationList;
        }
        
        
        /**
         * Creates a new conversation in the database and return the new conversation index
         * @param string $title
         * @param string $sanitizedMessage
         * @param int $senderUserIndex
         * @param int[] $receipientIndices
         * @return bool|int
         */
        public function createConversation(string $title, string $sanitizedMessage, int $senderUserIndex, array $receipientIndices): bool|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $receipientIndices[] = $senderUserIndex;
            
            $conversationIndex = -1;
            $db->insert('t_conversations', [
                'a_title' => $title,
                'a_create_user' => $senderUserIndex,
                'a_create_time' => time(),
                'a_lastpost_user' => $senderUserIndex,
                'a_lastpost_time' => time()
            ], $conversationIndex);
            
            
            foreach($receipientIndices as $memberIndex)
            {
                $db->insert('t_conversation_members', [
                    'a_conversation_index' => $conversationIndex,
                    'a_member_index' => $memberIndex
                ]);
            }
            
            $db->insert('t_conversation_messages', [
                'a_conversation_index' => $conversationIndex,
                'a_member_index' => $senderUserIndex,
                'a_ip_address' => $_SERVER['REMOTE_ADDR'],
                'a_post_date' => time(),
                'a_message' => $sanitizedMessage
            ]);
            
            return $conversationIndex;
        }
        
        
        /**
         * Gets information about a conversation from the database
         * @param int $conversationIndex
         * @return false|ConversationInfo
         */
        public function getConversationInfo(int $conversationIndex): false|ConversationInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $result = $db->select("
                SELECT
                    c.a_conversation_index,
                    c.a_title,
                    cm.a_lastread,
                    m.a_member_index,
                    m.a_displayname,
                    g.a_prefix,
                    g.a_suffix

                FROM 
                    t_conversations c,
                    t_conversation_members cm,
                    t_members m,
                    t_member_groups g
                    
                WHERE c.a_conversation_index = :conversation_index
                AND cm.a_conversation_index = c.a_conversation_index
                AND m.a_member_index = cm.a_member_index
                AND g.a_group_index = m.a_group_index", [
                    ':conversation_index' => $conversationIndex
                ]
            );

            if(!$result)
                return false;
            
            
            $conversationInfo = new ConversationInfo();
            $isSet = false;

            foreach($result as $r)
            {
                if(!$isSet)
                {
                    $conversationInfo->index = $r['a_conversation_index'];
                    $conversationInfo->title = $r['a_title'];
                    $isSet = true;
                }
                
                $conversationMember = new ConversationInfoMember();
                $conversationMember->index = $r['a_member_index'];
                $conversationMember->url = URL . 'usercp/' . $r['a_member_index'];
                $conversationMember->name = $r['a_displayname'];
                $conversationMember->prefix = $r['a_prefix'];
                $conversationMember->suffix = $r['a_suffix'];
                $conversationMember->lastRead = Utils::processTimestamp($r['a_lastread']);
                
                $conversationInfo->members[$conversationMember->index] = $conversationMember;

            }

            return $conversationInfo;
        }
        
        
        /**
         * Gets the amount of messages in a conversation from the database
         * @param $conversationIndex
         * @return int
         */
        public function getMessageCount($conversationIndex): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;

            $r = $db->selectOne("
                SELECT
                    count(*) as a_count

                FROM
                    t_conversation_messages pm
                
                WHERE pm.a_conversation_index = :conversation_index", [
                    ':conversation_index' => $conversationIndex,
                ]
            );

            return $r['a_count'];
        }
        
        
        /**
         * @param int $conversationIndex
         * @param int $page
         * @return false|ConversationMessage[]
         */
        public function getMessageList(int $conversationIndex, int $page): false|array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            
            $limit = Config::POSTS_PER_PAGE;
            $offset = ($page - 1) * $limit;                

            $query = sprintf("
                SELECT
                    p.a_message_index,
                    p.a_ip_address,
                    p.a_post_date,
                    p.a_message,
                    
                    m.a_member_index,
                    m.a_displayname,
                    m.a_avatar_url,
                    m.a_avatar_bgcolor,
                    m.a_posts as a_user_postcount,
                    
                    g.a_prefix,
                    g.a_suffix,
                    g.a_name as a_group_name

                FROM
                    t_conversation_messages p,
                    t_members m,
                    t_member_groups g
                
                WHERE p.a_conversation_index = :conversation_index
                AND p.a_member_index = m.a_member_index
                AND g.a_group_index = m.a_group_index
                ORDER BY p.a_post_date
                
                LIMIT %d
                OFFSET %d",
                $limit,
                $offset
            );

            $result = $db->select($query, [
                ':conversation_index' => $conversationIndex,
            ]);

            if(!$result)
                return false;

            /** @var ConversationMessage[] $messageList */
            $messageList = [];
            
            foreach($result as $r)
            {
                $message = new ConversationMessage();
                $message->index = $r['a_message_index'];
                $message->ipAddress = $r['a_ip_address'];
                $message->date = Utils::processTimestamp($r['a_post_date']);
                $message->message = $r['a_message'];
                
                $message->poster = new Poster();
                $message->poster->index = $r['a_member_index'];
                $message->poster->name = $r['a_displayname'];
                $message->poster->url = URL . 'usercp/view/' . $r['a_member_index'];
                $message->poster->avatarUrl = $r['a_avatar_url'];
                $message->poster->avatarBgColor = 'style="fill: ' . $r['a_avatar_bgcolor'] . '" ';
                $message->poster->groupName = $r['a_group_name'];
                $message->poster->prefix = $r['a_prefix'];
                $message->poster->suffix = $r['a_suffix'];
                $message->poster->postCount = $r['a_user_postcount'];
                
                $messageList[$message->index] = $message;
            }

            return $messageList;

        }
        
        
        /**
         * Adds a reply to a conversation
         * @param int $conversationIndex
         * @param string $sanitizedMessage
         * @param int $userIndex
         * @return false|int false when failed, message index when success
         */
        public function addMessage(int $conversationIndex, string $sanitizedMessage, int $userIndex): false|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $timestamp = time();
            
            $messageIndex = -1;
            $result = $db->insert('t_conversation_messages', [
                'a_conversation_index' => $conversationIndex,
                'a_member_index' => $userIndex,
                'a_ip_address' => $_SERVER['REMOTE_ADDR'],
                'a_post_date' => $timestamp,
                'a_message' => $sanitizedMessage
            ], $messageIndex);
            
            if($result === false)
                return false;
            
            
            $db->update("
                UPDATE
                    t_conversations t

                SET
                    t.a_posts = t.a_posts + 1,
                    t.a_lastpost_user = :user_index,
                    t.a_lastpost_time = :timestamp

                WHERE t.a_conversation_index = :conversation_index", [
                    ':conversation_index' => $conversationIndex,
                    ':user_index' => $userIndex,
                    ':timestamp' => $timestamp
                ]
            );
            
            return $messageIndex;
        }
        
        
        /**
         * Sets in the database the last time a user visited a conversation
         * @param int $conversationIndex
         * @param int $userIndex
         * @return bool
         */
        public function registerConversationVisit(int $conversationIndex, int $userIndex): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            
            return $db->update("
                UPDATE t_conversation_members
                SET
                    a_lastread = :timestamp
                
                WHERE a_member_index = :member_index
                AND a_conversation_index = :conversation_index", [
                ':timestamp' => time(),
                ':member_index' => $userIndex,
                ':conversation_index' => $conversationIndex
            ]);
        }
        
        
        /**
         * Takes in an array of display names and checks those against the database
         * When all names are found, the $userIndices will contain all the userIndex
         *
         * @param string[] $displayNames
         * @param int[] &$userIndices
         * @return false|string[] false if database error, string[] contains failed displayNames
         */
        public function getUserIndicesFromDisplaynames(array $displayNames, array &$userIndices): false|array
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $placeholders = implode(',', array_fill(0, count($displayNames), '?'));
            $query = "SELECT a_member_index, a_displayname FROM t_members WHERE a_displayname IN (" . $placeholders . ")";
            
            if(!$dbh = $db->prepare($query))
                return false;
            
            if(!$dbh->execute($displayNames))
                return false;
            
            $foundMembers = [];
            while($r = $dbh->fetch())
            {
                $foundMembers[$r['a_displayname']] = $r['a_member_index'];
            }
            
            if(count($foundMembers) != count($displayNames))
            {
                $failedNames = [];
                foreach($displayNames as $displayName)
                {
                    if(!isset($foundMembers[$displayName]))
                        $failedNames[] = $displayName;
                }
                
                return $failedNames;
            }
            
            foreach($foundMembers as $userIndex)
                $userIndices[] = $userIndex;
            
            return [];
            
        }
        
        
        /**
         * Gets the amount of unread conversations
         * @param int $userIndex
         * @return int
         */
        public function getUnreadConversationCount(int $userIndex): int
        {
            if(!$db = $this->getDatabaseConnection())
                return 0;
            
            $r = $db->selectOne("
                SELECT
                    COUNT(*) AS a_num

                FROM
                    t_conversation_members cm,
                    t_conversations c
                    
                WHERE cm.a_member_index = :member_index
                AND c.a_conversation_index = cm.a_conversation_index
                AND c.a_lastpost_time > cm.a_lastread", [
                    ':member_index' => $userIndex
                ]
            );
            
            if(!$r)
                return 0;
            
            return $r['a_num'];
        }
        
    }