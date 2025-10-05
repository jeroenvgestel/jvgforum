<?php
    
    class ConversationMessage
    {
        public int $index;
        public string $ipAddress;
        public string $date;
        public string $message;
        public Poster $poster;
    }