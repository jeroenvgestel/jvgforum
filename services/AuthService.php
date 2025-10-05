<?php
    
    class AuthService extends Service
    {
        private AuthRepository $authRepository;
        
        public function __construct(AuthRepository $authRepository)
        {
            $this->authRepository = $authRepository;
        }
        
        
        /**
         * Logs a user out and removes the session and cookies
         * @param User $user
         * @return void
         */
        public function logoutUser(User $user): void
        {
            if(isset($_COOKIE[Config::COOKIE_NAME]))
            {
                $this->authRepository->deleteCookie($user->cookieKey);
                setcookie(Config::COOKIE_NAME, '', 1, '/');
            }
            
            $_SESSION[Config::SESSION_NAME] = '';
            
            $user->reset();
        }
        
        
        /**
         * Tries to login the user
         * @param string $username
         * @param string $password
         * @param bool $setCookie
         * @return Response
         */
        public function tryLoginUser(string $username, string $password, bool $setCookie): Response
        {
            $userIndex = $this->authRepository->verifyLoginDetails($username, $password);
            if ($userIndex === false)
            {
                return Response::Fail('Invalid username or password');
            }
            
            $user = User::Instance();
            if (!$this->loginUser($userIndex, $user, $setCookie))
            {
                return Response::Fail('Unable to login user');
            }
            
            return Response::Success('Ok', URL);
        }
        
        
        /**
         * Checks if the user has a valid cookie or session and gets the data from the database
         * @param User $user
         * @return bool
         */
        public function checkIsUserLoggedIn(User $user): bool
        {
            // If we don't have a session, we will see if there is a cookie instead
            if (!isset($_SESSION[Config::SESSION_NAME]))
            {
                $cookieData = $this->validateCookie();
                if($cookieData === false)
                    return false;
                
                $userIndex = $cookieData['userIndex'];
                $cookieKey = $cookieData['cookieKey'];
                
                $verifiedUserIndex = $this->authRepository->verifyCookieKey($userIndex, $cookieKey);
                if($verifiedUserIndex === false)
                    return false;
                
                $user->cookieKey = $cookieKey;
                
                return $this->loginUser($verifiedUserIndex, $user);
            }
            
            
            $sessionValue = $_SESSION[Config::SESSION_NAME] ?? null;
            
            if (!is_array($sessionValue))
                return false;
            
            $userIndex = $sessionValue['mid'] ?? -1;
            $ip = $sessionValue['ip'] ?? '';
            
            
            // Check for session hijacking by verifying the IP
            if($ip != $_SERVER['REMOTE_ADDR'])
                return false;
            
            return $this->LoginUser($userIndex, $user);
        }
        
        
        /**
         * Gets the data from the cookie and validates if the data is like we expect it to be
         * @return false|array
         */
        public function validateCookie(): false|array
        {
            if (!isset($_COOKIE[Config::COOKIE_NAME]))
                return false;
            
            // Associate = true to preven object injection
            $cookieValue = json_decode($_COOKIE[Config::COOKIE_NAME], true);
            if (!$cookieValue)
                return false;
            
            
            // If its not an array, the decode has failed
            if (!is_array($cookieValue))
                return false;
            
            
            // Verify if the userIndex is infact a positive integer
            $userIndex = $cookieValue['mid'] ?? 0;
            $userIndex = filter_var($userIndex, FILTER_VALIDATE_INT, ['min_range' => 1]);
            
            if ($userIndex === false)
                return false;
            
            
            // Verify if the key is a valid alfanumeric
            $cookieKey = $cookieValue['key'] ?? '';
            $cookieKey = filter_var($cookieKey, FILTER_VALIDATE_REGEXP, ['options' => ['regexp' => '/^[a-zA-Z0-9]+$/',]]);
            
            if ($cookieKey === false)
                return false;
            
            if (strlen($cookieKey) != Config::COOKIE_KEY_SIZE)
                return false;
            
            
            // Verify if the cookie was tampered with
            $signature = $cookieValue['hmac'] ?? '';
            $expectedSignature = hash_hmac('sha256', json_encode([
                'mid' => $userIndex,
                'key' => $cookieKey
            ]), Config::COOKIE_HMAC_KEY);
            
            if (!hash_equals($signature, $expectedSignature))
                return false;
            
            
            return [
                'userIndex' => $userIndex,
                'cookieKey' => $cookieKey
            ];
        }
        
        
        /**
         * Create a cookie to save the user login
         * @param User $user
         * @return void
         */
        public function createCookie(User $user): void
        {
            $cookieKey = Utils::generateSecureRandomKey(Config::COOKIE_KEY_SIZE);
            $expireTime = time() + Config::COOKIE_EXPIRE_TIME;
            
            $signature = hash_hmac('sha256', json_encode([
                'mid' => $user->index,
                'key' => $cookieKey
            ]), Config::COOKIE_HMAC_KEY);
            
            
            $cookieValue = json_encode([
                'mid' => $user->index,
                'key' => $cookieKey,
                'hmac' => $signature,
            ]);
            
            
            $result = setcookie(Config::COOKIE_NAME, $cookieValue, [
                'expires' => $expireTime,
                'path' => '/',
                'httponly' => true,
                //'secure' => true,
                'samesite' => 'Strict'
            ]);
            
            if(!$result)
                return;
            
            $this->authRepository->storeCookie($user->index, $cookieKey, $expireTime);
            
        }
        
        
        /**
         * Create a session to keep the user logged in untli the browser closes
         * @param User $user
         * @return void
         */
        public function createSession(User $user): void
        {
            $_SESSION[Config::SESSION_NAME] = [
                'mid' => $user->index,
                'ip' => $_SERVER['REMOTE_ADDR']
            ];
        }
        
        
        /**
         * Gets all the user details and creates a authenticated user
         * @param $userIndex
         * @param User $user
         * @param bool $setCookie
         * @return bool
         */
        public function loginUser($userIndex, User $user, bool $setCookie = false): bool
        {
            if(!$this->authRepository->setUserInfo($userIndex, $user))
                return false;
            
            $this->createSession($user);
            
            $this->authRepository->updateUserLastActive($user->index);

            if($setCookie)
                $this->createCookie($user);
            
            
            
            // TODO: This should be somewhere else
            $conversationService = new ConversationService(
                new ConversationRepository()
            );
            
            $unreadConversations = $conversationService->getUnreadConversationCount($user->index);
            $user->inbox = $unreadConversations;
            
            return true;
        }
    }