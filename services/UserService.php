<?php
    
    class UserService extends Service
    {
        private UserRepository $userRepository;
        
        public function __construct(UserRepository $userRepository)
        {
            $this->userRepository = $userRepository;
        }
        
        public function getUserInfo(int $userIndex): false|UserInfo
        {
            $userInfo = $this->userRepository->getUserInfo($userIndex);
            if($userInfo === false)
            {
                return false;
            }
            
            return $userInfo;
        }
    }