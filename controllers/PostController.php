<?php

    class PostController extends Controller
    {
        /**
         * The route to take to display a edit post form
         * @param int $postIndex
         * @return void
         */
        public function edit(int $postIndex): void
        {
            $postService = ServiceFactory::createPostService();
            
            $postInfo = $postService->getPostInfo($postIndex);
            if($postInfo === false)
            {
                $this->View->renderError('No post found with this id.');
                return;
            }
            
            if($postInfo->canEdit === false)
            {
                $this->View->renderError('You don\'t have permission to edit this post.');
                return;
            }
            
            
            Breadcrumbs::addCategory($postInfo->category->name, $postInfo->category->index);
            Breadcrumbs::addForum($postInfo->forum->name, $postInfo->forum->index);
            Breadcrumbs::addTopic($postInfo->topic->title, $postInfo->topic->index);
            Breadcrumbs::add("Edit Post", "post/edit/" . $postInfo->index);

            $this->View->render('post/edit', [
                'post_info' => $postInfo,
                'sub_title' => 'Edit post in ' . $postInfo->topic->title,
            ], true);
        }
        
        
        /**
         * The route to take when a edit post form is sent to the server
         * @return void
         */
        public function update(): void
        {
            $this->requireJSON();
            
            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }
            
            if(!isset($postData['post_index']) || !Utils::isNumeric($postData['post_index']))
            {
                Response::Fail('Unknown topic')->sendJSON();
                return;
            }
            
            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }
            
            
            $postService = ServiceFactory::createPostService();
            
            
            $response = $postService->tryUpdatePost($postData['post_index'], $postData['message']);
            $response->sendJSON();
        }
        
        
        /**
         * The route to take when a post is hidden or showed
         * @param int $postIndex
         * @return void
         */
        public function togglehide(int $postIndex):void
        {
            $postService = ServiceFactory::createPostService();
            
            $response = $postService->toggleHide($postIndex);
            $response->sendJSON();
        }
        
        
        /**
         * The route to take when like post is clicked
         * it will send back a json array with the new likes of this post
         * @param int $postIndex
         * @return void
         */
        public function like(int $postIndex): void
        {
            $postService = ServiceFactory::createPostService();
            
            $response = $postService->likePost($postIndex);
            $response->sendJSON();
        }        


    }
    