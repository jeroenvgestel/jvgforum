<?php
    
    class Forum
    {
        public int $index;
        public int $type;
        public string $name;
        public string $desc;
        public ?Forum $parent = null;
    }