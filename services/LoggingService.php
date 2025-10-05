<?php
    
    class LoggingService extends Service
    {
        private LoggingRepository $loggingRepository;
        
        public function __construct(LoggingRepository $loggingRepository)
        {
            $this->loggingRepository = $loggingRepository;
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
            return $this->loggingRepository->addModerationLog($action, $forumIndex, $topicIndex, $postIndex, $memberIndex);
        }
    }