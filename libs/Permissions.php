<?php

    class Permissions
    {
        const int SEE_FORUM = 0;
        const int READ_TOPICS = 1;
        const int POST_NEW_TOPICS = 2;
        const int REPLY_TO_TOPICS = 3;
        const int MODERATE_FORUM = 4;
        const int COUNT = 5;

        private static ?array $permissions = null;
        private static ?array $empty = null;
        
        
        /**
         * Gets the permission set of a specific forum
         * @param int $forumIndex
         * @return bool[]
         */
        private static function GetPermissionsForForum(int $forumIndex): array
        {
            if(self::$permissions == null)
            {
                self::$permissions = include(__DIR__ . '/../cache/forum_permissions_cache.php');

                self::$empty = [];
                for($i=0; $i<self::COUNT; $i++)
                    self::$empty[$i] = false;
            }

            $user = User::Instance();
            
            if(isset(self::$permissions[$forumIndex][$user->group->index]))
                return self::$permissions[$forumIndex][$user->group->index];
        
            return self::$empty;
        }
        
        
        /**
         * Checks if a user can see a forum
         * @param int $forumIndex
         * @return bool
         */
        public static function CanSeeForum(int $forumIndex): bool
        {
            $permissions = self::GetPermissionsForForum($forumIndex);

            return $permissions[Permissions::SEE_FORUM];
        }
        
        
        /**
         * Checks if a user can read the topics in this forum
         * @param int $forumIndex
         * @return bool
         */
        public static function CanReadTopics(int $forumIndex): bool
        {
            $permissions = self::GetPermissionsForForum($forumIndex);

            return 
                $permissions[Permissions::SEE_FORUM] &&
                $permissions[Permissions::READ_TOPICS];
        }
        
        
        /**
         * Checks if a user has the rights to post in a forum
         * @param int $forumIndex
         * @return bool
         */
        public static function CanPostTopics(int $forumIndex): bool
        {
            $permissions = self::GetPermissionsForForum($forumIndex);

            return 
                $permissions[Permissions::SEE_FORUM] &&
                $permissions[Permissions::POST_NEW_TOPICS];
        }
        
        
        /**
         * Checks if the user has permission to reply in this forum
         * @param int $forumIndex
         * @param bool $isTopicClosed
         * @param bool $isTopicHidden
         * @return bool
         */
        public static function CanReplyTopic(int $forumIndex, bool $isTopicClosed, bool $isTopicHidden): bool
        {
            $permissions = self::GetPermissionsForForum($forumIndex);

            if(!$permissions[Permissions::SEE_FORUM])
                return false;

            if(!$permissions[Permissions::READ_TOPICS])
                return false;

            if(!$permissions[Permissions::REPLY_TO_TOPICS])
                return false;

            $forumModerator = Permissions::CanModerateForum($forumIndex);
            if($forumModerator === true)
                return true;

            if($isTopicClosed)
                return false;

            if($isTopicHidden)
                return false;

            return true;
        }
        
        
        /**
         * Checks if a user is a moderator in this forum
         * @param $forumIndex
         * @return bool
         */
        public static function CanModerateForum($forumIndex): bool
        {
            $user = User::Instance();
            if($user->isAdministrator || $user->isModerator)
                return true;

            $permissions = self::GetPermissionsForForum($forumIndex);

            return $permissions[Permissions::MODERATE_FORUM];
        }
        
        
        /**
         * Checks if a user can edit a post in this forum
         * @param int $forumIndex
         * @param int $postMemberIndex
         * @param bool $isPostDeleted
         * @param bool $isPostHidden
         * @param bool $isTopicClosed
         * @param int $postDate
         * @return bool
         */
        public static function CanEditPost(int $forumIndex, int $postMemberIndex, bool $isPostDeleted, bool $isPostHidden, bool $isTopicClosed, int $postDate): bool
        {
            $forumModerator = Permissions::CanModerateForum($forumIndex);
            if($forumModerator === true)
                return true;

            $user = User::Instance();

            if($isPostDeleted)
                return false;

            if($postMemberIndex == $user->index )
            {
                if($isTopicClosed)
                    return false;

                if($postDate < time() - Config::ALLOWED_EDIT_TIME)
                    return false;

                if($isPostHidden)
                    return false;

                return true;
            }

            return false;
        }
        
        
        /**
         * Checks if the user has the right to hide a post in this forum
         * @param int $forumIndex
         * @param int $postMemberIndex
         * @param bool $isPostDeleted
         * @param bool $is_post_hidden
         * @param bool $isTopicClosed
         * @return bool
         */
        public static function CanHidePost(int $forumIndex, int $postMemberIndex, bool $isPostDeleted, bool $is_post_hidden, bool $isTopicClosed): bool
        {
            $forumModerator = Permissions::CanModerateForum($forumIndex);
            if($forumModerator === true)
                return true;

            $user = User::Instance();

            if($user->isModerator)
                return true;

            if($user->isAdministrator)
                return true;

            if($isPostDeleted)
                return false;

            if($postMemberIndex == $user->index && Config::MEMBER_CAN_HIDE_OWN_POST)
            {
                if($isTopicClosed)
                    return false;

                if($is_post_hidden)
                    return false;

                return true;
            }

            return false;
        }


    }