<?php

    class PostInfo
    {
        public int $index;
        public string $message;
        public int $userIndex;
        public bool $canEdit;
        
        public PostInfoTopic $topic;
        public PostInfoForum $forum;
        public PostInfoCategory $category;
        
    }