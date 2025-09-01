<?php
    
    class ConversationService extends Service
    {
        private ConversationModel $conversationModel;
        
        public function __construct(ConversationModel $conversationModel)
        {
            $this->conversationModel = $conversationModel;
        }
        
        
        /**
         * Gets a list of conversations from the model
         * @param int $page
         * @return false|Conversation[]
         */
        public function getConversationList(int $page): false|array
        {
            $user = User::Instance();
            
            $conversationList = $this->conversationModel->getConversationList($page, $user->index);
            if($conversationList === false)
            {
                return false;
            }
            
            // Check if there are new posts since the last visit
            $lastViewTime = $user->lastMarkAllRead;
            foreach($conversationList as $conversation)
            {
                if($conversation->memberLastVisit > $lastViewTime)
                    $lastViewTime = $conversation->memberLastVisit;
                
                $hasNewPosts = $conversation->lastPoster->timeStamp > $lastViewTime;
                $conversation->hasNewPosts = $hasNewPosts;
            }
            
            return $conversationList;
        }
        
        
        /**
         * Fetches the amount of conversations and return a pagination
         * @param int $page Current Page
         * @return PaginationInfo
         */
        public function getConversationPagination(int $page): PaginationInfo
        {
            $user = User::Instance();
            
            $conversationCount = $this->conversationModel->getConversationCount($user->index);
            
            return new PaginationInfo(
                url: URL . 'inbox/view/',
                pageCount: ceil($conversationCount / Config::TOPICS_PER_PAGE),
                currentPage: $page
            );
            
        }
        
        
        /**
         * Gets information about a specific conversation
         * @param int $conversationIndex
         * @return false|ConversationInfo false if it doesn't exist
         */
        public function getConversationInfo(int $conversationIndex): false|ConversationInfo
        {
            $conversationInfo = $this->conversationModel->getConversationInfo($conversationIndex);
            if($conversationInfo === false)
            {
                return false;
            }
            
            return $conversationInfo;
        }
        
        
        /**
         * Gets the pagination info for the messages in a conversation
         * @param int $conversationIndex
         * @param int $page
         * @return PaginationInfo
         */
        public function getMessagePagination(int $conversationIndex, int $page = 1): PaginationInfo
        {
            $postCount = $this->conversationModel->getMessageCount($conversationIndex);
            
            return new PaginationInfo(
                url: URL . 'conversation/view/' . $conversationIndex,
                pageCount: ceil($postCount / Config::POSTS_PER_PAGE),
                currentPage: $page
            );
        }
        
        
        /**
         * Gets a list of messages in a conversation
         * @param int $conversationIndex
         * @param int $page
         * @return false|ConversationMessage[]
         */
        public function getMessageList(int $conversationIndex, int $page): false|array
        {
            $messageList = $this->conversationModel->getMessageList($conversationIndex, $page);
            if($messageList === false)
            {
                return false;
            }
            
            return $messageList;
        }
        
        
        /**
         * Sets the lastvisit time of a user in a conversation
         * @param int $conversationIndex
         * @param int $userIndex
         * @return void
         */
        public function registerConversationVisit(int $conversationIndex, int $userIndex): void
        {
            $this->conversationModel->registerConversationVisit($conversationIndex, $userIndex);
        }
        
        
        /**
         * Tries to add a message to a conversation
         * @param int $conversationIndex
         * @param int $userIndex
         * @param string $sanitizedMessage
         * @return Response
         */
        public function tryAddMessage(int $conversationIndex, int $userIndex, string $sanitizedMessage): Response
        {
            $conversationInfo = $this->getConversationInfo($conversationIndex);
            if($conversationInfo === false)
            {
                return Response::Fail('Conversation could not be found!');
            }
            
            if(!isset($conversationInfo->members[$userIndex]))
            {
                return Response::Fail('You are not part of this conversation');
            }
            
            $messageIndex = $this->conversationModel->addMessage($conversationIndex, $sanitizedMessage, $userIndex);
            if($messageIndex === false)
            {
                return Response::Fail('Message could not be added, please try again later.');
            }
            
            return Response::Success('OK', 'conversation/view/' . $conversationInfo->index . '#post' . $messageIndex);
        }
        
        
        /**
         * Tries to create a new conversation
         * @param int $userIndex
         * @param string $title
         * @param string $sanitizedMessage
         * @param string[] $receipients
         * @return Response
         */
        public function tryAddConversation(int $userIndex, string $title, string $sanitizedMessage, array $receipients): Response
        {
            $userIndices = [];
            $failedReceipients = $this->conversationModel->getUserIndicesFromDisplaynames($receipients, $userIndices);
            if($failedReceipients === false)
            {
                return Response::Fail('An error occured while creating the conversation, please try again later!');
            }
            
            if(count($failedReceipients) != 0)
            {
                $response = Response::Fail("One or more receipients don't exist");
                $response->data = $failedReceipients;
                return $response;
            }
            
            $conversationIndex = $this->conversationModel->createConversation($title, $sanitizedMessage, $userIndex, $userIndices);
            if($conversationIndex === false)
            {
                return Response::Fail('An error occured while creating the conversation, please try again later!');
            }
            
            return Response::Success('OK', 'conversation/view/' . $conversationIndex);
            
        }
        
        
        /**
         * Gets the amount of unread conversations from the databse
         * @param int $userIndex
         * @return int
         */
        public function getUnreadConversationCount(int $userIndex): int
        {
            return $this->conversationModel->getUnreadConversationCount($userIndex);
        }
    }