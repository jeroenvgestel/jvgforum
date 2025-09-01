<?php
    
    class Breadcrumbs
    {
        /** @var Crumb[] $data  */
        private static Array $data = [];
        
        
        /**
         * Since we have no static constructors, still..
         * @return void
         */
        private static function init(): void
        {
            self::$data = [
                new Crumb('Home', URL)
            ];
        }
        
        
        /**
         * Gets all the breadcrumbs to display
         * @return Crumb[]
         */
        public static function getBreadcrumbs(): Array
        {
            if(count(self::$data) === 0)
                self::init();
            
            return self::$data;
        }
        
        
        /**
         * Adds a normal breadcrumb
         * @param string $text
         * @param string $url
         * @return void
         */
        public static function add(string $text, string $url): void
        {
            if(count(self::$data) === 0)
                self::init();
            
            self::$data[] = new Crumb($text, URL . $url);
        }
        
        
        /**
         * Add a category breadcrumb
         * @param string $categoryName
         * @param int $categoryIndex
         * @return void
         */
        public static function addCategory(string $categoryName, int $categoryIndex): void
        {
            self::add($categoryName, 'forum/' . $categoryIndex);
        }
        
        
        /**
         * Add a forum breadcrumb
         * @param string $forumName
         * @param int $forumIndex
         * @return void
         */
        public static function addForum(string $forumName, int $forumIndex): void
        {
            self::add($forumName, 'forum/' . $forumIndex);
        }
        
        
        /**
         * Add a topic breadcrumb
         * @param string $topicTitle
         * @param int $topicIndex
         * @return void
         */
        public static function addTopic(string $topicTitle, int $topicIndex): void
        {
            self::add($topicTitle, 'topic/' . $topicIndex);
        }
        
        
        /**
         * Add a user breadcrumb
         * @param string $displayName
         * @param int $userIndex
         * @return void
         */
        public static function addUser(string $displayName, int $userIndex): void
        {
            self::add($displayName, 'usercp/' . $userIndex);
        }
        
        
        /**
         * Add a conversation breadcrumb
         * @param string $conversationTitle
         * @param int $conversationIndex
         * @return void
         */
        public static function addConversation(string $conversationTitle, int $conversationIndex): void
        {
            self::add($conversationTitle, 'conversation/view/' . $conversationIndex);
        }

    }