<?php
    
    class UserService extends Service
    {
        private UserModel $userModel;
        
        public function __construct(UserModel $userModel)
        {
            $this->userModel = $userModel;
        }
        
        public function getUserInfo(int $userIndex): false|UserInfo
        {
            $userInfo = $this->userModel->getUserInfo($userIndex);
            if($userInfo === false)
            {
                return false;
            }
            
            return $userInfo;
        }
    }