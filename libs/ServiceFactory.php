<?php
    
    class ServiceFactory
    {
        /**
         * @param AuthModel $authModel
         * @return AuthService
         */
        public static function createAuthService(
            AuthModel $authModel = new AuthModel()
        ): AuthService
        {
            return new AuthService($authModel);
        }
        
        
        /**
         * @param ConversationModel $conversationModel
         * @return ConversationService
         */
        public static function createConversationService(
            ConversationModel $conversationModel = new ConversationModel()
        ): ConversationService
        {
            return new ConversationService($conversationModel);
        }
        
        
        /**
         * @param ForumModel $forumModel
         * @param TopicModel $topicModel
         * @param PostModel $postModel
         * @return ForumService
         */
        public static function createForumService(
            ForumModel $forumModel = new ForumModel(),
            TopicModel $topicModel = new TopicModel(),
            PostModel $postModel = new PostModel()
        ): ForumService
        {
            return new ForumService($forumModel, $topicModel, $postModel);
        }
        
        
        /**
         * @return IndexService
         */
        public static function createIndexService(): IndexService
        {
            return new IndexService();
        }
        
        
        /**
         * @param LoggingModel $loggingModel
         * @return LoggingService
         */
        public static function createLoginService(
            LoggingModel $loggingModel = new LoggingModel()
        ): LoggingService
        {
            return new LoggingService($loggingModel);
        }
        
        
        /**
         * @param PostModel $postModel
         * @param TopicModel $topicModel
         * @param ForumModel $forumModel
         * @return PostService
         */
        public static function createPostService(
            PostModel $postModel = new PostModel(),
            TopicModel $topicModel = new TopicModel(),
            ForumModel $forumModel = new ForumModel()
        ): PostService
        {
            return new PostService($postModel, $topicModel, $forumModel);
        }
        
        
        /**
         * @param RegistrationModel $registrationModel
         * @return RegistrationService
         */
        public static function createRegistrationService(
            RegistrationModel $registrationModel = new RegistrationModel()
        ): RegistrationService
        {
            return new RegistrationService($registrationModel);
        }
        
        
        /**
         * @param TopicModel $topicModel
         * @param PostModel $postModel
         * @param ForumModel $forumModel
         * @return TopicService
         */
        public static function createTopicService(
            TopicModel $topicModel = new TopicModel(),
            PostModel $postModel = new PostModel(),
            ForumModel $forumModel = new ForumModel()
        ): TopicService
        {
            return new TopicService($topicModel, $postModel, $forumModel);
        }
        
        
        /**
         * @param UserModel $userModel
         * @return UserService
         */
        public static function createUserService(
            UserModel $userModel = new UserModel()
        ): UserService
        {
            return new UserService($userModel);
        }
        
        
    }