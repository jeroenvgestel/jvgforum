<?php
    
    class Topic
    {
        public int $index;
        public string $title;
        public string $url;
        public int $forumIndex;
        public string $forumName;
        public string $forumUrl;
        public string $posts;
        public string $views;
        public bool $isPinned;
        public bool $isClosed;
        public bool $isHidden;
        public bool $hasNewPosts;
        public Poster $starter;
        public ?Poster $lastpost;
    }
