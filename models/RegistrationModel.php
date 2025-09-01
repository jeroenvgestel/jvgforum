<?php
    
    class RegistrationModel extends MysqlModel
    {
        /**
         * Checks in the database if the username is available to use
         * @param string $username
         * @return bool
         */
        public function isUsernameAvailable(string $username): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("SELECT count(*) as a_num FROM t_members WHERE a_username = :username", [
                ':username' => $username
            ]);
            
            if(!$r)
                return false;
            
            return $r['a_num'] == 0;
        }
        
        
        /**
         * Checks in the database if the email address is available to use
         * @param string $email
         * @return bool
         */
        public function isEmailAvailable(string $email): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("SELECT count(*) as a_num FROM t_members WHERE a_email = :email", [
                ':email' => $email
            ]);
            
            if(!$r)
                return false;
            
            return $r['a_num'] == 0;
        }
        
        
        /**
         * Gets the registration data that belongs with the verification code or false if not found
         * @param string $verificationCode
         * @return bool|RegistrationInfo
         */
        public function getRegistrationInfo(string $verificationCode): bool|RegistrationInfo
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $r = $db->selectOne("SELECT * FROM t_registrations WHERE a_code = :verificationCode", [
                ':verificationCode' => $verificationCode
            ]);
            
            if(!$r)
                return false;
            
            $registrationInfo = new RegistrationInfo();
            $registrationInfo->index = $r['a_index'];
            $registrationInfo->username = $r['a_username'];
            $registrationInfo->passwordHash = $r['a_password_hash'];
            $registrationInfo->email = $r['a_email'];
            $registrationInfo->ip = $r['a_ip'];
            
            return $registrationInfo;
        }
        
        
        /**
         * Creates a user registration in the database that needs to be email validated first
         * @param string $username
         * @param string $password
         * @param string $email
         * @return false|string
         */
        public function createPendingUser(string $username, string $password, string $email): false|string
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $code = Utils::generateSecureRandomKey(Config::REGISTRATION_VERIFICATION_KEY_SIZE);
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);
            
            $result = $db->insert('t_registrations', [
                'a_username' => $username,
                'a_password_hash' => $passwordHash,
                'a_email' => $email,
                'a_ip' => $_SERVER['REMOTE_ADDR'],
                'a_code' => $code,
                'a_timestamp' => time()
            ]);
            
            if(!$result)
                return false;
            
            return $code;
        }
        
        
        /**
         * Creates a real user based on the registration information of a pending user
         * @param RegistrationInfo $registrationInfo
         * @return bool
         */
        public function activateUser(RegistrationInfo $registrationInfo): bool
        {
            if(!$db = $this->getDatabaseConnection())
                return false;
            
            $result = $db->insert('t_members', [
                'a_group_index' => Config::USERGROUP_MEMBERS,
                'a_username' => $registrationInfo->username,
                'a_password_hash' => $registrationInfo->passwordHash,
                'a_displayname' => $registrationInfo->username,
                'a_email' => $registrationInfo->email,
                'a_registration_date' => time(),
                'a_ip_address' => $_SERVER['REMOTE_ADDR'],
                'a_avatar_bgcolor' => Utils::getRandomAvatarColor()
            ]);
            
            if(!$result)
                return false;
            
            
            $db->update("DELETE FROM t_registrations WHERE a_index = :index OR a_timestamp < :timestamp", [
                ':index' => $registrationInfo->index,
                ':timestamp' => time() - (60*60*48)
            ]);
            
            return true;
            
        }
    }