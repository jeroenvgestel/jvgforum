<?php

    class TopicController extends Controller
    {

        /**
         * The route that displays a specific page of a topic
         * @param int $topicIndex
         * @param int $page
         * @return void
         */
        public function view(int $topicIndex, int $page = 1): void
        {
            $topicService = ServiceFactory::createTopicService();


            $topicInfo = $topicService->getInfo($topicIndex);
            if($topicInfo === false)
            {
                $this->View->renderError('No topic found with this id');
                return;
            }


            if(!Permissions::CanReadTopics($topicInfo->forum->index))
            {
                $this->View->renderError('You don\'t have permission to view this topic');
                return;
            }


            if($topicInfo->isHidden && !Permissions::CanModerateForum($topicInfo->forum->index))
            {
                $this->View->renderError('You don\'t have permission to view this topic');
                return; 
            }


            if(!Utils::isNumeric($page) || $page < 1)
                $page = 1;            

            $pagination = $topicService->getPagination($topicInfo->index, $page);           

            $postlist = $topicService->getPostList($topicInfo->index, $page, $topicInfo);

            $user = User::instance();
            if($user->isLoggedIn())
                $topicService->registerTopicVisit($topicIndex, $user->index);

            $sub_text = '';
            if($topicInfo->isHidden)
                $sub_text .= '<i class="warning"><b>Warning:</b> This topic is hidden!</i>';

            if($topicInfo->isClosed)
            {
                if(strlen($sub_text) > 0) 
                    $sub_text .= '<br>';

                $sub_text .= '<i class="warning"><b>Warning:</b> This topic is closed!</i>';
            }


            Breadcrumbs::addCategory($topicInfo->category->name, $topicInfo->category->index);
            Breadcrumbs::addForum($topicInfo->forum->name, $topicInfo->forum->index);
            Breadcrumbs::addTopic($topicInfo->title, $topicInfo->index);


            $this->View->render('topic/view', [
                'pagination' => $pagination,
                'topic_info' => $topicInfo,
                'postlist' => $postlist,
                'sub_title' => $topicInfo->title,
                'sub_text' => $sub_text,
            ], true);
        }


        /**
         * The route to land on when sending the form for adding a post to a topic
         * Taken from the stdin as JSON
         *
         * @return void
         */
        public function addreply(): void
        {
            $this->requireJSON();

            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }

            if(!isset($postData['topic_index']) || !Utils::isNumeric($postData['topic_index']))
            {
                Response::Fail('Unknown topic')->sendJSON();
                return;
            }

            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }
            
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->tryAddReply(
                $postData['topic_index'],
                $postData['message']
            );

            $response->sendJSON();               
        }


        /**
         * Route to display the page to edit the topic
         *
         * @param integer $topicIndex
         * @return void
         */
        public function edit(int $topicIndex): void
        {
            $topicService = ServiceFactory::createTopicService();


            $topicInfo = $topicService->getInfo($topicIndex);
            if($topicInfo === false)
            {
                $this->View->renderError('No topic found with this id');
                return;
            }

            $editInfo = $topicService->getInfoForEdit($topicInfo->index);


            $this->View->render('topic/edit', [
                'edit_info' => $editInfo,
                'topic_info' => $topicInfo,
                'sub_title' => $topicInfo->title,
                'sub_text' => 'Edit this topic'
            ], true);            
        }


        /**
         * Route to take when POSTing the edit topic form as JSON
         *
         * @return void
         */
        public function saveedit(): void
        {
            $this->requireJSON();

            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }

            if(!isset($postData['topic_index']) || !Utils::isNumeric($postData['topic_index']))
            {
                Response::Fail('Unknown forum')->sendJSON();
                return;
            }

            if(!isset($postData['post_index']) || !Utils::isNumeric($postData['post_index']))
            {
                Response::Fail('Unknown post')->sendJSON();
                return;
            }

            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }

            if(!isset($postData['title']) || strlen($postData['title']) < 3)
            {
                Response::Fail('Unknown title')->sendJSON();
                return;
            }
            
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->tryEditTopic(
                $postData['topic_index'],
                $postData['title'],
                $postData['post_index'],
                $postData['message']
            );

            $response->sendJSON();               

        }


        /**
         * Route to use to hide a topic async
         * @param $topic_index
         * @return void
         */
        public function toggleHide($topic_index): void
        {
            $this->requireJSON();
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->toggleHide($topic_index);
            $response->sendJSON();               
        }


        /**
         * Route to use to pin a topic async
         * @param $topic_index
         * @return void
         */
        public function togglePin($topic_index): void
        {
            $this->requireJSON();
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->TogglePin($topic_index);
            $response->sendJSON();               
        }


        /**
         * Route to take to close a topic async
         * @param $topic_index
         * @return void
         */
        public function toggleClose($topic_index): void
        {
            $this->requireJSON();
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->ToggleClose($topic_index);
            $response->sendJSON();               
        }


        /**
         * Route to take to move a topic to a different forum
         * @param $topicIndex
         * @return void
         */
        public function move($topicIndex): void
        {
            $topicService = ServiceFactory::createTopicService();

            $topicInfo = $topicService->getInfo($topicIndex);
            if($topicInfo === false)
            {
                $this->View->renderError('No topic found with this id');
                return;
            }

            $forumList = $topicService->getForumListForMoveTopic();

            $this->View->render('topic/move', [
                'topic_info' => $topicInfo,
                'forum_list' => $forumList,
                'sub_title' => $topicInfo->title,
                'sub_text' => 'Move this topic to another forum'
            ], true);            
        }


        /**
         * Route that receives the POST data of the move topic form
         * @return void
         */
        public function domove(): void
        {
            $this->requireJSON();

            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }


            if(!isset($post['topic_index']) || !Utils::isNumeric($post['topic_index']))
            {
                Response::Fail('Unknown topic')->sendJSON();
                return;
            }

            if(!isset($post['forum_index']) || !Utils::isNumeric($post['forum_index']))
            {
                Response::Fail('Unknown forum')->sendJSON();
                return;
            }
            
            
            $topicService = ServiceFactory::createTopicService();

            $response = $topicService->TryMoveTopic(
                $postData['topic_index'],
                $postData['forum_index'],
            );

            $response->sendJSON();               
        }

    }
    