<?php
    
    class PostService extends Service
    {
        private PostModel $postModel;
        private TopicModel $topicModel;
        private ForumModel $forumModel;
        
        public function __construct(PostModel $postModel, TopicModel $topicModel, ForumModel $forumModel)
        {
            $this->postModel = $postModel;
            $this->topicModel = $topicModel;
            $this->forumModel = $forumModel;
        }
        
        
        /**
         * Get some basic metadata from a post
         * @param int $postIndex
         * @return false|PostInfoSmall
         */
        public function getPostInfoSmall(int $postIndex): false|PostInfoSmall
        {
            $postInfoSmall = $this->postModel->getPostInfoSmall($postIndex);
            if($postInfoSmall === false)
            {
                return false;
            }
            
            return $postInfoSmall;
        }
        
        
        /**
         * Gets all the information about a post
         * @param int $postIndex
         * @return false|PostInfo
         */
        public function getPostInfo(int $postIndex): false|PostInfo
        {
            if(!Utils::isNumeric($postIndex))
                return false;
            
            $postInfo = $this->postModel->getPostInfo($postIndex);
            if($postInfo === false)
            {
                return false;
            }
            
            return $postInfo;
        }
        
        
        /**
         * Processes all the data to like a post
         * @param int $postIndex
         * @return Response
         */
        public function likePost(int $postIndex): Response
        {
            $user = User::Instance();
            if(!$user->IsLoggedIn())
            {
                return Response::Fail('User not logged in', 401);
            }
            
            $postInfoSmall = $this->getPostInfoSmall($postIndex);
            if($postInfoSmall === false)
            {
                return Response::Fail('Post does not exist');
            }
            
            if($postInfoSmall->memberIndex == $user->index)
            {
                return Response::Fail('You can not like your own post');
            }
            
            if(!Permissions::CanSeeForum($postInfoSmall->forumIndex))
            {
                return Response::Fail('You don\'t have permission to like this post', 401);
            }
            
            if($postInfoSmall->isTopicClosed == 1)
            {
                return Response::Fail('You can not (un)like a post in a closed topic', 401);
            }
            
            
            if(!$this->postModel->toggleLike($postIndex, $user->index))
            {
                return Response::Fail('Failed to like post, please try again later', 500);
            }

            $likesData = $this->postModel->getLikes($postIndex);

            
            return Response::Success('', '', $likesData);
        }
        
        
        /**
         * Hide a topic that is shown, or show a topic that is hidden
         * Write to moderation log
         * @param int $postIndex
         * @return Response
         */
        public function toggleHide(int $postIndex): Response
        {
            $postInfoSmall = $this->getPostInfoSmall($postIndex);
            if(!$postInfoSmall)
            {
                return Response::Fail('No post info found');
            }
            
            $user = User::Instance();
            if(!$user->CanModerateForum($postInfoSmall->forumIndex))
            {
                return Response::Fail('Not allowed', 401);
            }
            
            
            if(!$this->postModel->setHide($postIndex, !$postInfoSmall->isHidden))
            {
                return Response::Fail('Failed to toggle', 500);
            }
           
            
            $this->topicModel->recountPostsInTopic($postInfoSmall->topicIndex);
            $this->topicModel->updateTopicLastPost($postInfoSmall->topicIndex);
            
            // Only create a new index cache when the info has changed
            if($this->forumModel->updateForumLastPost($postInfoSmall->forumIndex))
                Cache::SaveForumIndex();
            
            
            $setHide = $postInfoSmall->isHidden ? 'Show' : 'Hide';
            ModerationLog::write("Hide Post = $setHide", postIndex: $postInfoSmall->postIndex);
            
            return Response::Success();
        }
        
        
        /**
         * Mutate the data to update a post to the storage
         * @param int $postIndex
         * @param string $message
         * @return Response
         */
        public function tryUpdatePost(int $postIndex, string $message): Response
        {
            $user = User::Instance();
            if(!$user->IsLoggedIn())
            {
                return Response::Fail('You need to login before you can edit messages');
            }
            
            $sanitizedMessage = Sanitizer::Sanitize($message);
            if(strlen($sanitizedMessage) < 2)
            {
                return Response::Fail('Your message is too short!');
            }

            
            $postInfo = $this->getPostInfo($postIndex);
            if($postInfo === false)
            {
                return Response::Fail('Post does not exist');
            }
            
            if($postInfo->canEdit === false)
            {
                return Response::Fail('You are not allowed to edit this message');
            }
            
            if(!$this->postModel->updatePost($postIndex, $user->index, $sanitizedMessage))
            {
                return Response::Fail('Failed to update post', 500);
            }
            
            $page = $this->postModel->getPageInTopic($postInfo->index, $postInfo->topic->index);
            $redirect_url = URL . "topic/$postInfo->topic->index/$page#post$postInfo->index";
            
            return Response::Success('OK', $redirect_url);
        }
        
        
    }