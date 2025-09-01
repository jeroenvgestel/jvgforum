<?php
    
    class Crumb
    {
        public string $text;
        public string $url;
        
        public function __construct(string $text, string $url)
        {
            $this->text = $text;
            $this->url = $url;
        }
    }