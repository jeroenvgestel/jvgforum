<?php
    
    class LoggingService extends Service
    {
        private LoggingModel $loggingModel;
        
        public function __construct(LoggingModel $loggingModel)
        {
            $this->loggingModel = $loggingModel;
        }
        
        
        /**
         * Adds a record to the moderation log
         * @param string $action
         * @param int $forumIndex
         * @param int $topicIndex
         * @param int $postIndex
         * @param int $memberIndex
         * @return bool
         */
        public function addModerationLog(string $action, int $forumIndex, int $topicIndex, int $postIndex, int $memberIndex): bool
        {
            return $this->loggingModel->addModerationLog($action, $forumIndex, $topicIndex, $postIndex, $memberIndex);
        }
    }