<?php

    class ForumController extends Controller
    {

        /**
         * View the list of topics from a specific forum and page
         *
         * @param int $forumIndex
         * @param int $page
         * @return void
         */
        public function view(int $forumIndex, int $page = 1): void
        {
            if(!Permissions::CanSeeForum($forumIndex))
            {
                $this->View->renderError('You don\'t have permission to view this forum');
                return;
            }

            $forumService = ServiceFactory::createForumService();


            $forumInfo = $forumService->getInfo($forumIndex);
            if($forumInfo === false)
            {
                $this->View->renderError('No forum found with this id');
                return;
            }

            Breadcrumbs::addCategory($forumInfo->parent?->name, $forumInfo->parent?->index);
            Breadcrumbs::addForum($forumInfo->name, $forumInfo->index);            


            if(!Utils::isNumeric($page) || $page < 1)
                $page = 1;            

                
            $pagination = $forumService->getPagination($forumInfo->index, $page);
            $topicList = $forumService->getTopicList($forumInfo->index, $page);


            $this->View->render('forum/view', [
                'pagination' => $pagination,
                'forum_info' => $forumInfo,
                'topiclist' => $topicList,
                'sub_title' => $forumInfo->name,
                'sub_text' => $forumInfo->desc,
                'can_create_topic' => Permissions::CanPostTopics($forumIndex),
            ], true);
        }


        /**
         * The page that displays the form to create a new topic in a specific forum
         *
         * @param [type] $forumIndex
         * @return void
         */
        public function newtopic($forumIndex): void
        {
            if(!Utils::isNumeric($forumIndex))
            {
                $this->View->renderError('Invalid data provided');
                return;
            }
            
            if(!Permissions::CanPostTopics($forumIndex))
            {
                $this->View->renderError('You don\'t have permission to create topics in this forum');
                return;
            }
            
            
            $forumService = ServiceFactory::createForumService();


            $forumInfo = $forumService->getInfo($forumIndex);
            if($forumInfo === false)
            {
                $this->View->renderError('No forum found with this id');
                return;            
            }

            Breadcrumbs::addCategory($forumInfo->parent?->name, $forumInfo->parent?->index);
            Breadcrumbs::addForum($forumInfo->name, $forumInfo->index);
            Breadcrumbs::add('New Topic', 'forum/newtopic/' . $forumInfo->index);


            $this->View->render('forum/newtopic', [
                'forum_info' => $forumInfo,
                'sub_title' => 'Create New Topic in ' . $forumInfo->name,
            ], true);
        }


        /**
         * The page that received the form data to create a new topic
         * The data is received from a json post with fetch
         *
         * @return void
         */
        public function addtopic(): void
        {
            $this->requireJSON();


            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }
    
            if(!isset($postData['forum_index']) || !Utils::isNumeric($postData['forum_index']))
            {
                Response::Fail('Unknown Forum')->sendJSON();
                return;
            }

            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }

            if(!isset($postData['title']) || strlen($postData['title']) < 3)
            {
                Response::Fail('No title provided')->sendJSON();
                return;
            }
            
            
            $forumService = ServiceFactory::createForumService();

            $response = $forumService->tryAddTopic(
                $postData['forum_index'], 
                $postData['title'], 
                $postData['message']
            );

            $response->sendJSON();
            
        }
        

        /**
         * The page that displays the most recent topics that have new posts in them
         *
         * @param integer $page
         * @return void
         */
        public function recent(int $page = 1): void
        {
            if(!Utils::isNumeric($page) || $page < 1)
                $page = 1;      

            Breadcrumbs::add('Recent Content', 'forum/recent');
            
            
            $forumService = ServiceFactory::createForumService();


            $pagination = $forumService->getPaginationRecent($page);
            $topiclist = $forumService->getRecentTopicList($page);            

            $this->View->render('forum/recent', [
                'pagination' => $pagination,
                'topiclist' => $topiclist,
                'sub_title' => 'Recent Topics',
            ], true);
        }
        
        
        /**
         * Route to take to set all forums read
         * @return Response
         */
        public function getMarkAllRead(): Response
        {
            $user = User::Instance();
            if(!$user->checkLogin())
                return Response::Fail("You need to be logged in to use this feature", 401);
            
            $forumService = ServiceFactory::createForumService();
            
            if(!$forumService->markAllForumsRead($user->index))
            {
                return Response::Fail('Unable to mark forums read');
            }
            
            return Response::Success('OK');
            
        }

    }
    