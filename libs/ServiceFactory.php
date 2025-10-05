<?php
    
    class ServiceFactory
    {
        /**
         * @param AuthRepository $authRepository
         * @return AuthService
         */
        public static function createAuthService(
            AuthRepository $authRepository = new AuthRepository()
        ): AuthService
        {
            return new AuthService($authRepository);
        }
        
        
        /**
         * @param ConversationRepository $conversationRepository
         * @return ConversationService
         */
        public static function createConversationService(
            ConversationRepository $conversationRepository = new ConversationRepository()
        ): ConversationService
        {
            return new ConversationService($conversationRepository);
        }
        
        
        /**
         * @param ForumRepository $forumRepository
         * @param TopicRepository $topicRepository
         * @param PostRepository $postRepository
         * @return ForumService
         */
        public static function createForumService(
            ForumRepository $forumRepository = new ForumRepository(),
            TopicRepository $topicRepository = new TopicRepository(),
            PostRepository  $postRepository = new PostRepository()
        ): ForumService
        {
            return new ForumService($forumRepository, $topicRepository, $postRepository);
        }
        
        
        /**
         * @return IndexService
         */
        public static function createIndexService(): IndexService
        {
            return new IndexService();
        }
        
        
        /**
         * @param LoggingRepository $loggingRepository
         * @return LoggingService
         */
        public static function createLoginService(
            LoggingRepository $loggingRepository = new LoggingRepository()
        ): LoggingService
        {
            return new LoggingService($loggingRepository);
        }
        
        
        /**
         * @param PostRepository $postRepository
         * @param TopicRepository $topicRepository
         * @param ForumRepository $forumRepository
         * @return PostService
         */
        public static function createPostService(
            PostRepository  $postRepository = new PostRepository(),
            TopicRepository $topicRepository = new TopicRepository(),
            ForumRepository $forumRepository = new ForumRepository()
        ): PostService
        {
            return new PostService($postRepository, $topicRepository, $forumRepository);
        }
        
        
        /**
         * @param RegistrationRepository $registrationRepository
         * @return RegistrationService
         */
        public static function createRegistrationService(
            RegistrationRepository $registrationRepository = new RegistrationRepository()
        ): RegistrationService
        {
            return new RegistrationService($registrationRepository);
        }
        
        
        /**
         * @param TopicRepository $topicRepository
         * @param PostRepository $postRepository
         * @param ForumRepository $forumRepository
         * @return TopicService
         */
        public static function createTopicService(
            TopicRepository $topicRepository = new TopicRepository(),
            PostRepository  $postRepository = new PostRepository(),
            ForumRepository $forumRepository = new ForumRepository()
        ): TopicService
        {
            return new TopicService($topicRepository, $postRepository, $forumRepository);
        }
        
        
        /**
         * @param UserRepository $userRepository
         * @return UserService
         */
        public static function createUserService(
            UserRepository $userRepository = new UserRepository()
        ): UserService
        {
            return new UserService($userRepository);
        }
        
        
    }