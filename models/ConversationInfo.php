<?php
    
    class ConversationInfo
    {
        public int $index;
        public string $title;
        
        /** @var ConversationInfoMember[] $members */
        public array $members;
    }