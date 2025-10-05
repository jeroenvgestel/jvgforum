<?php

    class UserController extends Controller
    {
        /**
         * This controller displays the member profile page
         * @param int $memberIndex
         * @return void
         */
        public function getMemberProfile(int $memberIndex): void
        {
            if(!Utils::isNumeric($memberIndex))
            {
                $this->View->renderError('No user found with this id');
                return;
            }
            
            
            $userService =new UserService(
                new UserRepository()
            );
            
            $userInfo = $userService->getUserInfo($memberIndex);
            if($userInfo === false)
            {
                $this->View->renderError('No user found with this id');
                return;
            }
            
            
            Breadcrumbs::addUser($userInfo->name, $userInfo->userIndex);

            
            $this->View->render('usercp/view', [
                'member_info' => $userInfo,
                'sub_title' => $userInfo->prefix . $userInfo->name . $userInfo->suffix,
                'sub_text' => 'User Profile',            
            ], true);
        }

    }
    