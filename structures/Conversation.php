<?php
    
    class Conversation
    {
        public int $index;
        public string $title;
        public string $url;
        public string $posts;
        public bool $hasNewPosts;
        public int $memberLastVisit;
        public Poster $startPoster;
        public Poster $lastPoster;
    }