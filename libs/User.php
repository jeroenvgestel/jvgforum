<?php

    class User 
    {
        /**
         * @var ?User $user;
         */
        private static ?User $user = null;

        public static function Instance(): User
        {
            if(self::$user == null)
                self::$user = new User();

            return self::$user;
        }        


        public int $index;
        public string $username;
        public string $displayname;
        public string $email;
        public bool $login;
        public string $avatarUrl;
        public string $avatarBgColor;
        public int $lastMarkAllRead;
        public bool $isAdministrator;
        public bool $isModerator;
        public UserGroup $group;
        public int $inbox;
        public string $cookieKey;


        public function __construct()
        {
            $this->reset();
        }

        public function reset(): void
        {
            $this->index = -1;
            $this->username = '';
            $this->displayname = '';
            $this->email = '';
            $this->login = false;
            $this->avatarUrl = '';
            $this->avatarBgColor = '';
            $this->lastMarkAllRead = time();
            $this->isAdministrator = false;
            $this->isModerator = false;
            $this->group = UserGroup::Guest();
            $this->inbox = 0;
            $this->cookieKey = '';
        }

        public function isLoggedIn(): bool
        {
            return $this->login === true;
        }

        public function checkLogin(): bool
        {
            $authService = new AuthService(
                new AuthRepository()
            );
            
            return $authService->checkIsUserLoggedIn($this);
        }

        public function CanModerateForum(int $forum_index): bool
        {
            if(!$this->IsLoggedIn())
                return false;

            if($this->isAdministrator || $this->isModerator)
                return true;

            return Permissions::CanModerateForum($forum_index);
        }



    }