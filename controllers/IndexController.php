<?php

    class IndexController extends Controller
    {
        /**
         * The main forum index route that displays a cached version of the forum index
         *
         * @return void
         */
        public function index(): void
        {
            $indexService = ServiceFactory::createIndexService();
            $forumData = $indexService->getMainForumData();

            $this->View->render('index/index', [
                'forum_data' => $forumData
            ], true);


        }

        /**
         * Displays the children of a specific subforum
         *
         * @param integer $forum_index
         * @return void
         */
        public function view(int $forum_index): void
        {
            $indexService = SserviceFactory::createIndexService();
            $forumData = $indexService->getOneForumChildren($forum_index);

            if($forumData === false)
            {
                $this->View->renderError('No forum found with this id');
                return;
            }

            Breadcrumbs::addCategory($forumData['name'], $forumData['index']);

            $this->View->render('index/single', [
                'forum_data' => [$forumData],
                'sub_title' => $forumData['name'],
            ]);
        }


    }
    