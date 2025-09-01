<?php

    class Response
    {
        public bool $success;
        public string $message;
        public string $redirect;
        public int $errorCode;
        public Array $data = [];

        
        /**
         * Creates a failed response used for API purposes
         * @param string $message This is the error message that will be displayed
         * @param int $errorCode The http error code to send
         * @return Response
         */
        public static function Fail(string $message, int $errorCode = 400): Response
        {
            $response = new Response;
            $response->success = false;
            $response->redirect = '';
            $response->message = $message;
            $response->errorCode = $errorCode;

            return $response;
        }

        
        /**
         * Creates a success response used for API purposes
         * @param string $message This is the error message that may be displayed (optional)
         * @param string $redirect This is the url that may be redirected to (optional)
         * @param array $data sometimes data can be needed
         * @return Response
         */        
        public static function Success(string $message = '', string $redirect = '', Array $data = []): Response
        {
            $response = new Response;
            $response->success = true;
            $response->redirect = $redirect;
            $response->message = $message;
            $response->errorCode = 200;
            $response->data = $data;

            return $response;
        }
        
        
        /**
         * Sends the response to the browser as JSON
         * @param bool $bExit If the code should exit after its set, default = true
         * @return void
         */
        public function sendJSON(bool $bExit = true): void
        {
            http_response_code($this->errorCode);
            header('Content-Type: application/json; charset=UTF-8');

            echo(json_encode($this));   

            if($bExit)
                exit();
        }
    }