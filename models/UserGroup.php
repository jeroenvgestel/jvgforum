<?php

    class UserGroup
    {
        public int $index;
        public string $name;
        public string $prefix;
        public string $suffix;
        public bool $isModerator;
        public bool $isAdministrator;

        public static function Guest(): UserGroup
        {
            $group = new UserGroup();
            $group->index = 2;
            $group->name = 'Guests';
            $group->prefix = '';
            $group->suffix = '';
            $group->isModerator = false;
            $group->isAdministrator = false;

            return $group;
        }
    }