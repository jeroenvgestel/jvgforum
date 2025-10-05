<?php

    class ModerationLog
    {
        
        /**
         * Writes a moderator action to the database
         * @param string $action
         * @param int $forumIndex
         * @param int $topicIndex
         * @param int $postIndex
         * @param int $memberIndex
         * @return bool
         */
        public static function write(string $action, int $forumIndex = 0, int $topicIndex = 0, int $postIndex = 0, int $memberIndex = 0): bool
        {
            $loggingService = new LoggingService(
                new LoggingRepository()
            );
            
            return $loggingService->addModerationLog($action, $forumIndex, $topicIndex, $postIndex, $memberIndex);
        }




        
    }