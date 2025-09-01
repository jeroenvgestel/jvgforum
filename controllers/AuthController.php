<?php
    
    class AuthController extends Controller
    {
        /**
         * Renders the login form if the user is not logged in
         * @return void
         */   
        public function getLoginForm(): void
        {
            Breadcrumbs::add('Login', 'login');
            
            $user = User::Instance();
            if($user->checkLogin() === true)
            {
                $this->View->renderError('You are already logged in!');
                return;
            }
            
            $this->View->render('login/form');
        }
        
        
        /**
         * Handles the post data of the login form
         * @return void
         */
        public function postLoginForm(): void
        {
            
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if(!$username || strlen($username) < 3)
            {
                $this->View->render('login/form', [
                    'error' => 'Username not entered'
                ]);
                
                return;
            }
            
            $password = filter_input(INPUT_POST, 'password');
            if(!$password || strlen($password) < 3)
            {
                $this->View->render('login/form', [
                    'error' => 'Password not entered'
                ]);
                
                return;
            }
            
            $setCookie = false;
            if(filter_input(INPUT_POST, 'rememberme') === 'yes')
                $setCookie = true;
            
           
            $user = User::Instance();
            if($user->checkLogin() === true)
            {
                Response::Fail('You are already logged in!');
                return;
            }
            
            $authService = ServiceFactory::createAuthService();
            
            
            $response = $authService->tryLoginUser($username, $password, $setCookie);
            if($response->success === true)
            {
                header('Location: ' . $response->redirect);
                return;
            }
            
            
            $this->View->render('login/form', [
                'error' => $response->message
            ]);
            
        }
        
        
        /**
         * Logs out the user and redirect to the index page
         * @return void
         */
        public function getLogout(): void
        {
            $authService = ServiceFactory::createAuthService();
            
            $user = User::Instance();
            $authService->logoutUser($user);
            
            header("location: " . URL);
        }
        
        
        /**
         * Not implemented (yet))
         * @return void
         * @throws Exception
         */
        public function getLostPasswordForm(): void
        {
            throw new Exception('Not implemented');
        }
        
    }