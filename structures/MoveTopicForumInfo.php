<?php
    
    class MoveTopicForumInfo
    {
        public int $forumIndex;
        public int $type;
        public int $parentIndex;
        public string $name;
        
        /** @var MoveTopicForumInfo[] */
        public Array $children = [];
    }
