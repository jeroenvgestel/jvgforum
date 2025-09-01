<?php

    class TopicService extends Service
    {
        private TopicModel $topicModel;
        private PostModel $postModel;
        private ForumModel $forumModel;

        public function __construct(TopicModel $topicModel, PostModel $postModel, ForumModel $forumModel)
        {
            $this->topicModel = $topicModel;
            $this->postModel = $postModel;
            $this->forumModel = $forumModel;
        }

        
        /**
         * Used to verify en mutate all the data to create a new post in a topic.
         *
         * @param integer $topicIndex
         * @param string $message Will be sanitized vs whitelist
         * @return Response To send back as JSON
         */
        public function tryAddReply(int $topicIndex, string $message): Response
        {
            $user = User::Instance();
            if(!$user->IsLoggedIn())
            {
                return Response::Fail('You need to login before you can post messages', 401);
            }

            $sanitizedMessage = Sanitizer::Sanitize($message);

            if(strlen($sanitizedMessage) < 2)
            {
                return Response::Fail('Your message is too short!');
            }


            if(!$this->topicModel->userCanPostInTopic($topicIndex))
            {
                return Response::Fail('You are not allowed to post a message in this topic', 401);
            }

            $postIndex = $this->postModel->addReplyToTopic($user->index, $topicIndex, $sanitizedMessage);
            if($postIndex === false)
            {
                return Response::Fail('Your reply could not be added, please try again later');
            }

            $this->postModel->increaseUserPostCount($user->index);

            $redirect_url = URL . 'topic/' . $topicIndex . '#post' . $postIndex;

            return Response::Success('OK', $redirect_url);
        }


        /**
         * Gets basic info about a topic, id, name, forum, category, etc
         *
         * @param integer $topicIndex
         * @return false|TopicInfo false if a topic does not exist
         */
        public function getInfo(int $topicIndex): false|TopicInfo
        {
            $topicInfo = $this->topicModel->getTopicInfo($topicIndex);
            if($topicInfo === false)
            {
                return false;
            }

            return $topicInfo;
        }
        
        
        /**
         * Get simple pagination info based on the post count in a topic
         * @param int $topicIndex
         * @param int $page
         * @return PaginationInfo
         */
        public function getPagination(int $topicIndex, int $page = 1): PaginationInfo
        {
            $post_count = $this->topicModel->getPostCount($topicIndex);

            return new PaginationInfo(
                url: URL . 'topic/' . $topicIndex,
                pageCount: ceil($post_count / Config::POSTS_PER_PAGE),
                currentPage: $page
            );
        }

        /**
         * Gets all the posts of a specific topic from the database
         * @param int $topicIndex
         * @param int $page
         * @param TopicInfo $topicInfo
         * @return false|Post[]
         */
        public function getPostList(int $topicIndex, int $page, TopicInfo $topicInfo): false|Array
        {
            return $this->postModel->getList($topicIndex, $page, $topicInfo);
        }
        
        
        /**
         * Get the information needed to edit a topic
         * @param int $topicIndex
         * @return false|EditTopicInfo
         */
        public function getInfoForEdit(int $topicIndex): false|EditTopicInfo
        {
            return $this->topicModel->getInfoForEdit($topicIndex);
        }


        /**
         * Mutates all the data received to update the topic and post in the database
         *
         * @param int $topicIndex
         * @param string $title Will be stripped of html tags
         * @param int $postIndex
         * @param string $message Will be sanitized in this function
         * @return Response To send back to the browser as JSON
         */
        public function tryEditTopic(int $topicIndex, string $title, int $postIndex, string $message): Response
        {
            $user = User::Instance();
            if(!$user->IsLoggedIn())
            {
                return Response::Fail('You need to login before you can post messages', 401);
            }


            $sanitizedMessage = $message;
            $title = strip_tags($title);

            if(strlen($sanitizedMessage) < 2)
            {
                return Response::Fail('Your message is too short!');
            }

            
            if(!$this->topicModel->userCanEditTopicandPost($topicIndex, $postIndex))
            {
                return Response::Fail('You are not allowed to edit this topic', 401);
            }

            $this->topicModel->updateTitle($topicIndex, $title);
            $this->postModel->updatePost($postIndex, User::Instance()->index, $sanitizedMessage);

            $redirectUrl = URL . 'topic/' . $topicIndex;

            return Response::Success('OK', $redirectUrl);
        }


        /**
         * Toggles the hide state of a topic. When done it recreates the forum index cache to
         * show the correct last post/user/topic
         * @param int $topicIndex
         * @return Response
         */
        public function toggleHide(int $topicIndex): Response
        {
            $topicInfo = $this->topicModel->getTopicInfo($topicIndex);
            if($topicInfo === false)
            {
                return Response::Fail('No topic found with this id');
            }

            if(!Permissions::CanModerateForum($topicInfo->forum->index))
            {
                return Response::Fail('You don\'t have permission to take this action');
            }

            $setTopicHidden = !$topicInfo->isHidden;
            $this->topicModel->setHide($topicInfo->index, $setTopicHidden);

            ModerationLog::write("Hide Topic = $setTopicHidden", topicIndex: $topicInfo->index);

            $this->forumModel->recountTopicsInForum($topicInfo->index);
            $this->forumModel->updateForumLastPost($topicInfo->forum->index);

            Cache::SaveForumIndex();

            return Response::Success();
        }


        /**
         * Pins a topic if its not pinned, or unpins it if its pinned
         * if you are allowed to
         * @param int $topicIndex
         * @return Response
         */
        public function togglePin(int $topicIndex): Response
        {
            $topicInfo = $this->topicModel->getTopicInfo($topicIndex);
            if($topicInfo === false)
            {
                return Response::Fail('No topic found with this id');
            }

            if(!Permissions::CanModerateForum($topicInfo->forum->index))
            {
                return Response::Fail('You don\'t have permission to take this action');
            }

            $setTopicPinned = !$topicInfo->isPinned;
            $this->topicModel->setPinned($topicInfo->index, $setTopicPinned);

            ModerationLog::write("Pin Topic = $setTopicPinned", topicIndex: $topicInfo->index);

            return Response::Success();
        }


        /**
         * Closes a topic if its open, or opens it when its closed, if you are allowed to
         * @param int $topicIndex
         * @return Response
         */
        public function ToggleClose(int $topicIndex): Response
        {
            $topicInfo = $this->topicModel->getTopicInfo($topicIndex);
            if($topicInfo === false)
            {
                return Response::Fail('No topic found with this id');
            }

            if(!Permissions::CanModerateForum($topicInfo->forum->index))
            {
                return Response::Fail('You don\'t have permission to take this action');
            }

            $setTopicClosed = !$topicInfo->isClosed;
            $this->topicModel->setClosed($topicInfo->index, $setTopicClosed);

            ModerationLog::write("Close Topic = $setTopicClosed", topicIndex: $topicInfo->index);

            return Response::Success();
        }
        
        
        /**
         * Just gets a list of forums that can be used to move a topic to
         * Permissions will be checked
         * @return false|MoveTopicForumInfo[]
         */
        public function getForumListForMoveTopic(): false|array
        {
            return $this->topicModel->getForumList();
        }


        /**
         * Handles all the data sent from the move topic form and sends it to the database
         * @param int $topicIndex
         * @param int $forumIndex
         * @return Response
         */
        public function tryMoveTopic(int $topicIndex, int $forumIndex): Response
        {
            $user = User::Instance();

            if(!$user->IsLoggedIn())
                return Response::Fail('You need to be logged in for this function');


            $topicInfo = $this->topicModel->getTopicInfo($topicIndex);
            if($topicInfo === false)
            {
                return Response::Fail('No topic found with this id');
            }

            if(!Permissions::CanModerateForum($topicInfo->forum->index))
                return Response::Fail('You don\'t have permission to move topics from this forum');

            if(!Permissions::CanSeeForum($forumIndex))
                return Response::Fail('You don\'t have permission to move topics to this forum');

            if(!$this->topicModel->moveTopic($topicInfo->index, $forumIndex))
                return Response::Fail('Failed to move topic');


            $this->forumModel->recountTopicsInForum($topicInfo->forum->index);
            $this->forumModel->updateForumLastPost($topicInfo->forum->index);
            
            $this->topicModel->recountPostsInTopic($topicInfo->index);
            $this->forumModel->updateForumLastPost($forumIndex);

            Cache::SaveForumIndex();

            return Response::Success('OK', URL . 'forum/' . $forumIndex);

        }
        
        public function registerTopicVisit(int $topicIndex, int $userIndex): void
        {
            $this->topicModel->registerTopicVisit($topicIndex, $userIndex);
        }
    }