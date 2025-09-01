<?php

    class Controller
    {

        public View $View;
        
        function __construct()
        {
            $this->View = new View();
            $this->View->Controller = $this;
        }
        
        /**
         * Verify if the content type = JSON, if not, terminate
         * @return void
         */
        protected function requireJSON(): void
        {
            $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';

            if ($contentType !== "application/json") 
                die('Wrong header');
        }

        
        /**
         * Reads the json data from stdin and decode into an array
         * @return false|Array false if json could not be parsed or empty.
         */
        protected function getJSONPost(): false|Array
        {
            $content = trim(file_get_contents("php://input"));
            $post = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) 
            {
                return false;
            }

            if(count($post) == 0)
            {
                return false;
            }

            return $post;
        }



    }
    