<?php

    /**
     * Manages all the operations that need to be done for the ForumController
     */
    class ForumService extends Service
    {
        private ForumModel $forumModel;
        private TopicModel $topicModel;
        private PostModel $postModel;


        public function __construct(ForumModel $forumModel, TopicModel $topicModel, PostModel $postModel)
        {
            $this->forumModel = $forumModel;
            $this->topicModel = $topicModel;
            $this->postModel = $postModel;
        }


        /**
         * Get basic info about a forum: id, name, description, parent
         * Can be used to verify if the forum exists.
         *
         * @param integer $forumIndex
         * @return false|Forum false if the forum does not exist
         */
        public function getInfo(int $forumIndex): false|Forum
        {
            $forumInfo = $this->forumModel->getInfo($forumIndex);
            if($forumInfo === false)
            {
                return false;
            }

            return $forumInfo;
        }


        /**
         * Used to get the data to show the pagination for the topics in a forum
         *
         * @param integer $forumIndex
         * @param integer $page Current page
         * @return PaginationInfo
         */
        public function getPagination(int $forumIndex, int $page = 1): PaginationInfo
        {
            $topicCount = $this->forumModel->getTopicCount($forumIndex);


            return new PaginationInfo(
                url: URL . 'forum/view/' . $forumIndex,
                pageCount: ceil($topicCount / Config::TOPICS_PER_PAGE),
                currentPage: $page
            );
        }


        /**
         * Used to get the list of topics in a specified forum
         * Permissions to see hidden topics are checked
         *
         * @param integer $forumIndex
         * @param integer $page
         * @return false|Topic[]
         */
        public function getTopicList(int $forumIndex, int $page = 1): false|Array
        {
            $topicList = $this->topicModel->getList($forumIndex, $page);
            if($topicList === false)
            {
                return false;
            }

            return $topicList;
        }


        /**
         * Used to get the data to show the pagination for the recent topics
         *
         * @param integer $page Current page
         * @return false|Array [url, page_count, current_page]
         */
        public function getPaginationRecent(int $page = 1): false|Array
        {
            $topic_count = $this->forumModel->getRecentTopicCount();

            return [
                'url' => URL . 'forum/recent',
                'page_count' => ceil($topic_count / Config::TOPICS_PER_PAGE),
                'current_page' => $page
            ];
        }


        /**
         * Used to get the list of the most recent topics in all forums
         * that are user has access to.
         * Permissions to see hidden topics are checked
         *
         * @param integer $page
         * @return false|Topic[]
         */
        public function getRecentTopicList(int $page = 1): false|Array
        {
            $topicList = $this->topicModel->getRecentList($page);
            if($topicList === false)
            {
                return false;
            }


            return $topicList;
        }


        /**
         * Used to verify en mutate all the data to create a new topic in a forum.
         *
         * @param integer $forumIndex
         * @param string $title Will be stripped of html tags
         * @param string $message Will be sanitized vs whitelist
         * @return Response To send back as JSON
         */
        public function tryAddTopic(int $forumIndex, string $title, string $message): Response
        {
            $user = User::Instance();
            if(!$user->IsLoggedIn())
            {
                return Response::Fail('You need to login before you can post messages', 401);
            }


            $sanitizedMessage = Sanitizer::Sanitize($message);
            $strippedTitle = strip_tags($title);


            if(strlen($sanitizedMessage) < 2)
            {
                return Response::Fail('Your message is too short!');
            }

            if(strlen($strippedTitle) < 2)
            {
                return Response::Fail('Your title is too short!');
            }


            if(!Permissions::CanPostTopics($forumIndex))
            {
                return Response::Fail('You are not allowed to create a topic in this forum', 401);
            }


            $forumInfo = $this->getInfo($forumIndex);
            if($forumInfo === false)
            {
                return Response::Fail('This forum does not exist.', 404);
            }

            if($forumInfo->type == FORUM_TYPE_CAT)
            {
                return Response::Fail('It is not possible to create a topic in a category.', 401);
            }


            $topicIndex = $this->topicModel->addTopic($user->index, $forumIndex, $title);
            if($topicIndex === false)
            {
                return Response::Fail('Your topic could not be added, please try again later');
            }

            $this->postModel->addReplyToTopic($topicIndex, $forumIndex, $sanitizedMessage);

            Cache::SaveForumIndex();

            $this->postModel->increaseUserPostCount($user->index);

            return Response::Success('OK', URL . 'forum/view/' . $forumIndex);

        }


        /**
         * Sets the last read time for this user to the current time
         * @param int $userIndex
         * @return bool
         */
        public function markAllForumsRead(int $userIndex): bool
        {
            return $this->forumModel->markAllForumsRead($userIndex);
        }

    }