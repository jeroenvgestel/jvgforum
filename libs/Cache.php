<?php

    class Cache
    {
        
        /**
         * Create a cache file from the forum permissions so that they don't have
         * to be loaded from the database every pageview
         * @return bool
         */
        public static function SavePermissions(): bool
        {
            if(!$db = Database::getConnection())
                return false;                      
        
            // Get a list of all forums
            if(!$result = $db->select("SELECT a_forum_index FROM t_forums"))
                return false;
            
            $forums = [];
            foreach($result as $r)
                $forums[] = $r['a_forum_index'];


            // Get a list of all groups
            if(!$result = $db->select("SELECT a_group_index FROM t_member_groups"))
                return false;

            $groups = [];
            foreach($result as $r)
                $groups[] = $r['a_group_index'];


            // Combine
            $permissions = [];
            foreach($forums as $forum_index)
            {
                foreach($groups as $group_index)
                {
                    for($type=0; $type<Permissions::COUNT; $type++)
                    {
                        $permissions[$forum_index][$group_index][$type] = false;
                    }
                }
            }


            if(!$result = $db->select("SELECT * FROM t_forum_permissions"))
                return false;


            foreach($result as $r)
            {
                $forum_index = $r['a_forum_index'];
                $group_index = $r['a_group_index'];
                $type = $r['a_type'];

                $permissions[$forum_index][$group_index][$type] = true;
            }

            $fileContent = "<?php\nreturn " . var_export($permissions, true) . ";\n";
            file_put_contents(__DIR__ . '/../cache/forum_permissions_cache.php', $fileContent);
            
            return true;
        }
        
        
        /**
         * Save the forum index into a cache file so that it is possible to view the forum index
         * without touching the database
         * @return bool
         */
        public static function SaveForumIndex(): bool
        {
            if(!$db = Database::getConnection())
                return false;

            $result = $db->select(/*sql*/"
                SELECT 
                    f.a_forum_index,
                    f.a_type,
                    f.a_parent_index,
                    f.a_name,
                    f.a_desc,
                    f.a_topic_count,
                    f.a_post_count,
                    f.a_lastpost_user,
                    f.a_lastpost_topic,
                    f.a_lastpost_time,
                    m.a_displayname,
                    m.a_avatar_url,
                    m.a_avatar_bgcolor,
                    g.a_prefix,
                    g.a_suffix,
                    t.a_title

                FROM 
                    t_forums f
                    LEFT JOIN t_members m ON m.a_member_index = f.a_lastpost_user
				    LEFT JOIN t_member_groups g ON m.a_group_index = g.a_group_index
                    LEFT JOIN t_topics t ON t.a_topic_index = f.a_lastpost_topic

                "
            );

            if(!$result)
                return false;

            $allforums = [];
            foreach($result as $r)
            {
                $allforums[$r['a_forum_index']] = [
                    'index' => $r['a_forum_index'],
                    'type' => $r['a_type'],
                    'parent_index' => $r['a_parent_index'],
                    'name' => $r['a_name'],
                    'desc' => $r['a_desc'],
                    'topic_count' => $r['a_topic_count'],
                    'post_count' => $r['a_post_count'],
                    'lastpost' => [
                        'user' => [
                            'name' => $r['a_displayname'],
                            'url' => URL . 'usercp/' . $r['a_lastpost_user'],
                            'style' => 'color:red;',
                            'avatarUrl' => $r['a_avatar_url'],
                            'avatarBgColor' => 'style="fill: ' . $r['a_avatar_bgcolor'] . '" ',
                            'prefix' => $r['a_prefix'],
                            'suffix' => $r['a_suffix'],                       
                        ],
                        'topic' => [
                            'index' => $r['a_lastpost_topic'],
                            'title' => $r['a_title'],
                            'url' => URL . 'topic/' . $r['a_lastpost_topic']
                        ],
                        'date' => Utils::processTimestamp($r['a_lastpost_time']),
                        'timestamp' => $r['a_lastpost_time'],
                    ],
                    'parent' => null,
                    'children' => [],
                    'url' => URL . 'forum/' . $r['a_forum_index']
                ];
            } 
            
            $fileContent = "<?php\nreturn " . var_export($allforums, true) . ";\n";
            $fileName = __DIR__ . '/../cache/forum_index_cache.php';


            file_put_contents($fileName, $fileContent);
            
            return true;
        }


        private static Array $forumIndexData = [];
        public static function GetForumIndex(): Array 
        {
            if(count(self::$forumIndexData) == 0)
            {
                self::$forumIndexData = include(__DIR__ . '/../cache/forum_index_cache.php');
            }

            return self::$forumIndexData;
        }
    }

