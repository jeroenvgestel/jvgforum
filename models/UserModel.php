<?php

    class UserModel extends MysqlModel
    {
        /**
         * Gets the user information from the database
         * @param int $userIndex
         * @return false|UserInfo
         */
        public function getUserInfo(int $userIndex): false|UserInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;

            $r = $db->selectOne("
                SELECT
                    m.a_member_index,
                    m.a_group_index,
                    m.a_displayname,
                    m.a_registration_date,
                    m.a_last_activity,
                    m.a_last_post,
                    m.a_posts,
                    m.a_avatar_url,
                    m.a_avatar_bgcolor,

                    g.a_name as a_group_name,
                    g.a_prefix,
                    g.a_suffix,
                    m.a_is_moderator OR g.a_is_moderator AS a_is_moderator,
                    m.a_is_administrator OR g.a_is_administrator AS a_is_administrator,
                    (SELECT COUNT(*) FROM t_likes l WHERE l.a_member_index = m.a_member_index) as a_likes
                
                FROM
                    t_members m,
                    t_member_groups g

                WHERE m.a_member_index = :member_index
                AND g.a_group_index = m.a_group_index", [
                    ':member_index' => $userIndex
                ]
            );

            if(!$r)
                return false;
            
            $userInfo = new UserInfo();
            $userInfo->userIndex = $r['a_member_index'];
            $userInfo->groupIndex = $r['a_group_index'];
            $userInfo->name = $r['a_displayname'];
            $userInfo->regDate = Utils::processTimestamp($r['a_registration_date']);
            $userInfo->lastActive = Utils::processTimestamp($r['a_last_activity']);
            $userInfo->lastPost = Utils::processTimestamp($r['a_last_post']);
            $userInfo->posts = $r['a_posts'];
            $userInfo->avatarUrl = $r['a_avatar_url'];
            $userInfo->avatarBgColor = 'style="fill: ' . $r['a_avatar_bgcolor'] . ';';
            $userInfo->groupName = $r['a_group_name'];
            $userInfo->prefix = $r['a_prefix'];
            $userInfo->suffix = $r['a_suffix'];
            $userInfo->isModerator = $r['a_is_moderator'];
            $userInfo->isAdministrator = $r['a_is_administrator'];
            $userInfo->likes = $r['a_likes'];
            
            return $userInfo;

        }
        
    }