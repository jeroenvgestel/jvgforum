<?php

    class ConversationController extends Controller
    {
        
        /**
         * Route to follow to show the inbox page with a list of conversations
         * @param int $page
         * @return void
         */
        public function conversationList(int $page = 1): void
        {
            $user = User::Instance();
            if($user->checkLogin() === false)
            {
                $this->View->renderError('You need to be logged in!');
                return;
            }
            
            if(!Utils::isNumeric($page))
                $page = 1;
            
            Breadcrumbs::add('Inbox', 'inbox');
            
            $conversationService = ServiceFactory::createConversationService();
            
            
            $pagination = $conversationService->getConversationPagination($page);
            $conversationList = $conversationService->getConversationList($page);
            
            $this->View->render('conversation/list', [
                'pagination' => $pagination,
                'conversationList' => $conversationList,
                'sub_title' => 'Private Messages',
                'sub_text' => 'Inbox'
            ], true);
        }
        
        
        /**
         * Route to take when opening a conversation to see the messages
         * @param int $conversationIndex
         * @param int $page
         * @return void
         */
        public function viewConversation(int $conversationIndex, int $page = 1): void
        {
            $user = User::Instance();
            if($user->checkLogin() === false)
            {
                $this->View->renderError('You need to be logged in!');
                return;
            }
            
            $conversationService = ServiceFactory::createConversationService();
            
            
            $conversationInfo = $conversationService->getConversationInfo($conversationIndex);
            if($conversationInfo === false)
            {
                $this->View->renderError('Could not find the conversation!');
                return;
            }
            
            
            if(!isset($conversationInfo->members[$user->index]))
            {
                $this->View->renderError('You are not part of this conversation!');
                return;
            }
            
            
            BreadCrumbs::add('Conversations', 'inbox');
            BreadCrumbs::addConversation($conversationInfo->title, $conversationInfo->index);


            if(!Utils::isNumeric($page))
                $page = 1;            


            $messagePagination = $conversationService->getMessagePagination($conversationIndex, $page);
            $messageList = $conversationService->getMessageList($conversationIndex, $page);
            
            $conversationService->registerConversationVisit($conversationIndex, $user->index);


            $this->View->render('conversation/view', [
                'pagination' => $messagePagination,
                'conversation_info' => $conversationInfo,
                'messageList' => $messageList,
                'sub_title' => $conversationInfo->title,
                'sub_text' => 'Private conversation',
            ], true);
        }
        
        
        /**
         * This controller method receives the json post data from the add message form
         * it will try to add the message to the conversation
         * @return void
         */
        public function addMessage(): void
        {
            $this->requireJSON();
            
            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }
            
            if(!isset($postData['conversation_index']) || !Utils::isNumeric($postData['conversation_index']))
            {
                Response::Fail('Unknown conversation')->sendJSON();
                return;
            }
            
            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }
            
            $user = User::Instance();
            if($user->checkLogin() === false)
            {
                Response::Fail('You need to be logged in!')->sendJSON();
                return;
            }
            
            
            $sanitizedMessage = Sanitizer::Sanitize($postData['message']);
            if(strlen($sanitizedMessage) < 2)
            {
                Response::Fail('Your message is too short')->sendJSON();
                return;
            }
            
            $conversationIndex = intval($postData['conversation_index']);
            
            
            $conversationService = ServiceFactory::createConversationService();

            $response = $conversationService->tryAddMessage($conversationIndex, $user->index, $sanitizedMessage);
            $response->sendJSON();
            
        }
        
        
        /**
         * Will display the form to create a new conversation
         * @return void
         */
        public function getCreateConversationForm(): void
        {
            Breadcrumbs::add('Inbox', 'inbox');
            Breadcrumbs::add('New message', 'inbox/newmessage');
            
            $this->View->render('conversation/create', [
                'sub_title' => 'Private Messages',
                'sub_text' => 'Start a new conversation'
            ], true);
        }
        
        
        /**
         * Will receive the json post data from the create conversation form
         * @return void
         */
        public function postCreateConversationForm(): void
        {
            $this->requireJSON();
            
            $postData = $this->getJSONPost();
            if($postData === false)
            {
                Response::Fail('JSON Data Error')->sendJSON();
                return;
            }
            
            
            if(!isset($postData['receipients']) || strlen($postData['receipients']) < 3)
            {
                Response::Fail('Unknown receipients')->sendJSON();
                return;
            }
            
            if(!isset($postData['title']) || strlen($postData['title']) < 3)
            {
                Response::Fail('Unknown title')->sendJSON();
                return;
            }
            
            if(!isset($postData['message']) || strlen($postData['message']) < 3)
            {
                Response::Fail('Unknown message')->sendJSON();
                return;
            }
            
            
            $user = User::Instance();
            if($user->checkLogin() === false)
            {
                Response::Fail('You need to be logged in!')->sendJSON();
                return;
            }
            
            
            $sanitizedMessage = Sanitizer::Sanitize($postData['message']);
            if(strlen($sanitizedMessage) < 2)
            {
                Response::Fail('Your message is too short!')->sendJSON();
                return;
            }
            
            $title = strip_tags($postData['title']);
            
            $receipientString = strip_tags($postData['receipients']);
            $receipients = preg_split('/\s*,\s*/', $receipientString, -1, PREG_SPLIT_NO_EMPTY);
            
            
            //TODO: Maybe change into a setting, where each usergroup can have different limits
            if(count($receipients) < 1 || count($receipients) > Config::PM_ALLOWED_RECEIPIENTS)
            {
                Response::Fail('You have to choose between 1 and 10 receipients.')->sendJSON();
                return;
            }
            
            foreach($receipients as &$receipient)
            {
                $receipient = trim($receipient);
                
                // a-Z 0-9 and spaces allowed
                if(!preg_match("/^\s*([a-zA-Z0-9]+(\s[a-zA-Z0-9]+)*)\s*$/", $receipient))
                {
                    Response::Fail('Invalid name found, names can only contain numbers/letters and spaces')->sendJSON();
                    return;
                }
            }
            
            $conversationService = ServiceFactory::createConversationService();
            
            $response = $conversationService->tryAddConversation(
                $user->index,
                $title,
                $sanitizedMessage,
                $receipients
            );
            
            $response->sendJSON();
        }

    }
    