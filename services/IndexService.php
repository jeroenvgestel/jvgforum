<?php

    class IndexService
    {
        //private ForumModel $forumModel;

        public function __construct(/*ForumModel $forumModel*/)
        {
            //$this->forumModel = $forumModel;
        }

        /**
         * Gets the entire cached forum index and applies the user rights to it
         *
         * @return Array
         */
        public function getMainForumData(): Array
        {
            $user = User::Instance();
            
            $allforums = Cache::GetForumIndex();

            $data = [];
            foreach($allforums as &$forum)
            {
                if(Permissions::CanSeeForum($forum['index']) === false)
                    continue;

                if($forum['parent_index'] == -1)
                {
                    $data[] = &$forum;
                    continue;
                }

                $parent = &$allforums[$forum['parent_index']];
                $parent['children'][$forum['index']] = &$forum;

                $forum['havenew'] = $user->lastMarkAllRead < $forum['lastpost']['timestamp'] ? 1 : 0;
            }

            return $data;

        }


        /**
         * Gets the children of one specific subforu
         *
         * @param int $forum_index
         * @return false|Array
         */
        public function getOneForumChildren(int $forum_index): false|Array
        {
            $data = $this->getMainForumData();

            foreach($data as $category)
            {
                if($category['index'] == $forum_index)
                    return $category;

                foreach($category['children'] as $forum)
                {
                    if($forum['index'] == $forum_index)
                        return $forum;

                    foreach($forum['children'] as $subforum)
                    {
                        if($subforum['index'] == $forum_index)
                            return $subforum;
                    }
                }
            }

            return false;
        }

    }