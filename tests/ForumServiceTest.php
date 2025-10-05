<?php
    
    declare(strict_types=1);
    
    use PHPUnit\Framework\MockObject\MockObject;
    use PHPUnit\Framework\TestCase;
    
    class ForumServiceTest extends TestCase
    {
        private MockObject $forumRepositoryMock;
        private MockObject $topicRepositoryMock;
        private MockObject $postRepositoryMock;
        
        private  ForumService $forumService;
        
        protected function setUp(): void
        {
            $this->forumRepositoryMock = $this->createMock(ForumRepository::class);
            $this->topicRepositoryMock = $this->createMock(TopicRepository::class);
            $this->postRepositoryMock = $this->createMock(PostRepository::class);
            
            $this->forumService = ServiceFactory::createForumService(
                $this->forumRepositoryMock,
                $this->topicRepositoryMock,
                $this->postRepositoryMock
            );
            
            $user = User::instance();
            $user->login = true;
            $user->index = 1;
        }
        
        
        public function test_getInfo(): void
        {
            $forum = new Forum();
            $forum->index = 1;
            $forum->type = 1;
            $forum->name = 'test';
            $forum->desc = 'test';
            $forum->parent = null;
            
            $this->forumRepositoryMock->method('getInfo')
                ->with(1)
                ->willReturn($forum);
            
            $forumInfo = $this->forumService->getInfo(1);
            
            $this->assertInstanceOf(Forum::class, $forumInfo);
        }
        
        
        public function test_getPagination(): void
        {
            $forumIndex = 1;
            $topicCount = 200;
            $pageCount = ceil($topicCount / Config::TOPICS_PER_PAGE);
            
            $this->forumRepositoryMock->method('getTopicCount')
                ->with($forumIndex)
                ->willReturn($topicCount);
            
            $paginationInfo = $this->forumService->getPagination($forumIndex);
            
            $this->assertInstanceOf(PaginationInfo::class, $paginationInfo);
            
            $this->assertEquals($paginationInfo->url, URL . 'forum/view/' . $forumIndex);
            $this->assertEquals($paginationInfo->pageCount, $pageCount);
        }
        
        
        
    }
