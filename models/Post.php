<?php
    
    class Post
    {
        public int $index;
        public string $ipAddress;
        public string $date;
        public string $message;
        public bool $isHidden;
        public Array $likes = [];
        public Poster $user;
        public bool $canDelete;
        public string $deleteUrl;
        public bool $canEdit;
        public string $editUrl;
        public Poster $lastEdit;
    }