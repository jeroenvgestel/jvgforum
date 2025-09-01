<?php
    
    class TopicInfo
    {
        public int $index;
        public string $title;
        public string $url;
        public bool $isClosed;
        public bool $isPinned;
        public bool $isHidden;
        public bool $canPost;
        
        public TopicInfoForum $forum;
        public TopicInfoCategory $category;
    }