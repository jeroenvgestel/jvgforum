<?php
    
    use JetBrains\PhpStorm\NoReturn;
    use Random\RandomException;
    
    class RegistrationController extends Controller
    {
        /**
         * The route displays the registration form if the user is not logged in
         * Method: GET
         * @return void
         */
        public function getRegistrationForm(): void
        {
            $user = User::Instance();
            if($user->checkLogin())
            {
                $this->View->renderError('You can not register a new account while logged in');
                return;
            }
            
            $this->View->render('registration/form', [
                'registration_form_post_url' => URL . 'register',
            ]);
        }
        
        
        /**
         * The controller that handles the registation form data
         * Must contain INPUT_POST[username, email, password1, password2, verification]
         * Method: POST
         * @return void
         */
        public function postRegistrationForm(): void
        {
            
            // Validate input Username
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if(!$username || strlen($username) < 3)
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'Username must be at least 3 characters long'
                ]);
                
                return;
            }
            
            // Validate input Email
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            if(!$email || strlen($email) < 3)
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'Email address is not valid, a real email address is needed to activate your account!'
                ]);
                
                return;
            }
            
            
            // Validate input Password
            $password = filter_input(INPUT_POST, 'password1');
            if(!$password || !preg_match_all("/^(?=.*[A-Za-z])(?=.*\d)(?=.*[^\w\s])[A-Za-z\d\S]{8,}$/", $password))
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'Password must be at least 8 characters, contain lowercase, uppercase, number and special character!'
                ]);
                
                return;
            }
            
            $password2 = filter_input(INPUT_POST, 'password2');
            if(!$password2 || $password !== $password2)
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'The two passwords are not the same, please enter the same password twice!'
                ]);
                
                return;
            }
            
            
            // Validate Human Verification
            // TODO: Consider using a real Captcha? (but basically it is only to stop registation floods)
            $verification = filter_input(INPUT_POST, 'verification', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if(!$verification || strlen($verification) != Config::CAPTCHA_KEY_LENGTH)
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'Please enter the Human Verification code'
                ]);
                
                return;
            }
            
            if($verification !== $_SESSION["security_answer"])
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => 'The human verification code is incorrect!'
                ]);
                
                return;
            }
            
            
            $registrationService = ServiceFactory::createRegistrationService();
            
            $response = $registrationService->registerUser($username, $password, $email);
            if(!$response->success)
            {
                $this->View->render('registration/form', [
                    'registration_form_post_url' => URL . 'register',
                    'error' => $response->message
                ]);
                
                return;
            }
            
            $this->View->renderSuccess('Your account is registered.<br>Please go to your email and click the link to activate your account!');
            
        }
        
        
        /**
         * Controller to use when the user clicks the validation link in the validation email
         * after account registration
         * Method: GET
         * @param string $verificationCode
         * @return void
         */
        public function getUseVerificationCode(string $verificationCode): void
        {
            if(strlen($verificationCode) != Config::REGISTRATION_VERIFICATION_KEY_SIZE)
            {
                $this->View->renderError('Verification code is not valid');
                return;
            }
            
            $registrationService = ServiceFactory::createRegistrationService();
            
            $response = $registrationService->validateEmailedVerificationCode($verificationCode);
            if(!$response->success)
            {
                $this->View->renderError($response->message);
                return;
            }
            
            $this->View->renderSuccess('Your account is activated<br>You can now log in!');
            
        }
        
        
        /**
         * Creates a simple Captcha like image
         * Method: GET
         * @return void
         */
        #[NoReturn]
        public function getCaptchaImage(): void
        {
            $availableCharacters = "abcdefghjkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789";
            
            $code = '';
            for($i=0; $i<Config::CAPTCHA_KEY_LENGTH; $i++)
            {
                $pos = 0;
                
                try
                {
                    $pos = random_int(0, strlen($availableCharacters) - 1);
                }
                catch (RandomException $e)
                {
                    error_log($e->getMessage());
                }
                
                $code .= $availableCharacters[$pos];
            }
            
            $code = trim($code);
            $imageText = implode(' ', str_split($code));
            
            $font = 6;
            $fontWidth = imagefontwidth($font);
            $fontHeight = imagefontheight($font);
            $textWidth = $fontWidth * strlen($imageText);
            
            $imageWidth = $textWidth + 20;
            $imageHeight = $fontHeight;
            
            $textCenterX = ceil(($imageWidth - $textWidth) / 2);
            $textCenterY = ceil(($imageHeight - $fontHeight) / 2);
            
            $_SESSION["security_answer"] = $code;
            
            $image = ImageCreate($imageWidth, $imageHeight);
            
            $backgroundColor = imagecolorallocate($image, 24, 24, 24);
            $textColor = imagecolorallocate($image, 0xe8, 0xcb, 0xb1);
            
            ImageFill($image, 0, 0, $backgroundColor);
            ImageString($image, $font, $textCenterX, $textCenterY, $imageText, $textColor);
            
            header("Content-Type: image/jpeg");
            ImageJpeg($image, null, 100);
            ImageDestroy($image);
            
            exit();
        }
    }