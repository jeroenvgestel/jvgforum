<?php
    
    class AuthModel extends MysqlModel
    {
        
        /**
         * Tests the username and password against the database and returns the userIndex when successful
         * @param string $username
         * @param string $password
         * @return false|int database error/wrong credentials|userIndex
         */
        public function verifyLoginDetails(string $username, string $password): false|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("
                SELECT
                    a_member_index,
                    a_password_hash
                    
                FROM
                    t_members
                    
                WHERE a_username = :username", [
                    ':username' => $username
                ]
            );
            
            if(!$r)
                return false;
            
            
            $passwordHash = $r['a_password_hash'];
            if(!password_verify($password, $passwordHash))
                return false;
            
            
            return $r['a_member_index'];
        }
        
        
        /**
         * Verifies if the cookie key is in the database with the correspronding member
         * @param int $userIndex
         * @param string $cookieKey
         * @return false|int false if not found, userIndex if found
         */
        public function verifyCookieKey(int $userIndex, string $cookieKey): false|int
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("SELECT a_member_index FROM t_member_cookies WHERE a_member_index = :userIndex AND a_key = :cookieKey", [
                ':userIndex' => $userIndex,
                ':cookieKey' => $cookieKey
            ]);
            
            if(!$r)
                return false;
            
            return $r['a_member_index'];
        }
        
        
        /**
         * Save the cookie information in the database with the userIndex and the cookieKey
         * @param int $userIndex
         * @param string $cookieKey
         * @param int $expireTime
         * @return bool
         */
        public function storeCookie(int $userIndex, string $cookieKey, int $expireTime): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->insert('t_member_cookies', [
                'a_expire_time' => $expireTime,
                'a_member_index' => $userIndex,
                'a_key' => $cookieKey
            ]);
        }
        
        
        /**
         * Gets the user data from the database and loads the user class with the data
         * @param int $userIndex
         * @param User $user
         * @return bool
         */
        public function setUserInfo(int $userIndex, User $user): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("
                SELECT
                    m.a_member_index,
                    m.a_username,
                    m.a_displayname,
                    m.a_email,
                    m.a_last_activity,
                    m.a_avatar_url,
                    m.a_avatar_bgcolor,
                    m.a_last_mark_read,
                    m.a_is_moderator OR g.a_is_moderator AS a_is_moderator,
                    m.a_is_administrator OR g.a_is_administrator AS a_is_administrator,
                    g.a_group_index,
                    g.a_name as a_group_name,
                    g.a_prefix as a_group_prefix,
                    g.a_suffix as a_group_suffix

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
            
            $user->index = $r['a_member_index'];
            $user->username = $r['a_username'];
            $user->displayname = $r['a_displayname'];
            $user->email = $r['a_email'];
            $user->login = true;
            $user->avatarBgColor = 'style="fill: ' . $r['a_avatar_bgcolor'] . '"';
            $user->avatarUrl = $r['a_avatar_url'];
            $user->lastMarkAllRead = $r['a_last_mark_read'];
            $user->isAdministrator = $r['a_is_administrator'] == 1;
            $user->isModerator = $r['a_is_moderator'] == 1;
            
            $user->group = new UserGroup();
            $user->group->index = $r['a_group_index'];
            $user->group->name = $r['a_group_name'];
            $user->group->prefix = $r['a_group_prefix'];
            $user->group->suffix = $r['a_group_suffix'];
            
            return true;
        }
        
        
        /**
         * Saves when the user was last active
         * @param int $userIndex
         * @return void
         */
        public function updateUserLastActive(int $userIndex): void
        {
            if(!$db = $this->getDatabaseConnection())
                return;
            
            $db->update("
                UPDATE t_members
                SET
                    a_last_activity = :timestamp
                WHERE
                    a_member_index = :member_index", [
                    ':timestamp' => time(),
                    ':member_index' => $userIndex
                ]
            );
        }
        
        
        /**
         * Remove the cookie data from the database
         * @param string $cookieKey
         * @return bool
         */
        public function deleteCookie(string $cookieKey): bool
        {
            if (!$db = $this->getDatabaseConnection())
                return false;
            
            return $db->update("
                DELETE FROM t_member_cookies
                WHERE a_key = :keycode OR a_expire_time > :expire_time", [
                    ':keycode' => $cookieKey,
                    ':expire_time' => time()
                ]
            );
        }
    }