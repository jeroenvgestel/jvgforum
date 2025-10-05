<?php
    
    class RegistrationService extends Service
    {
        private RegistrationRepository $registrationRepository;
        
        public function __construct(RegistrationRepository $registrationRepository)
        {
            $this->registrationRepository = $registrationRepository;
        }
        
        public function registerUser(string $userName, string $password, string $email): Response
        {
            if(!$this->registrationRepository->isUsernameAvailable($userName))
            {
                return Response::Fail("Username already exists");
            }
            
            if(!$this->registrationRepository->isEmailAvailable($email))
            {
                return Response::Fail("Email already exists");
            }
            
            $verificationCode = $this->registrationRepository->createPendingUser($userName, $password, $email);
            if($verificationCode === false)
            {
                return Response::Fail("Could not create user, please try again later");
            }
            
            if(!$this->sendRegistrationEmail($userName, $email, $verificationCode))
            {
                return Response::Fail("Validation email could not be sent, but account is created, please contact support!");
            }
            
            unset($_SESSION['security_answer']);
            
            return Response::Success('Your account is registered, please click the activation link in your Email!', URL . 'register/success');
        }
        
        
        public function validateEmailedVerificationCode(string $verificationCode): Response
        {
            $registrationInfo = $this->registrationRepository->getRegistrationInfo($verificationCode);
            if($registrationInfo === false)
            {
                return Response::Fail("Invalid activation code");
            }
            
            if(!$this->registrationRepository->activateUser($registrationInfo))
            {
                return Response::Fail("Could not activate user, please try again later");
            }
            
            return Response::Success('Your account is activated, you can now sign in!');
            
        }
        
        
        /**
         * Sends the email with the link
         * TODO: Use a library like PHPMailer?
         * @param string $userName
         * @param string $email
         * @param string $verificationCode
         * @return bool
         */
        private function sendRegistrationEmail(string $userName, string $email, string $verificationCode): bool
        {
            $link = URL . 'register/verify/' . $verificationCode;
            $subject = DEFAULT_TITLE . ' Account Activation Email';
            $message = sprintf('
                Dear %s,<br>
                Please click this link to activate your account:<br>
                <a href="%s">%s</a><br>
                <br><br>
                %s',
                $userName,
                $link, $link,
                DEFAULT_TITLE
            );
            
            return Utils::SendMail($email, $subject, $message);
        }
        
    }