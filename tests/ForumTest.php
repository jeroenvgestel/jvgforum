<?php

    declare(strict_types=1);
    
    use PHPUnit\Framework\TestCase;
    
    final class ForumTest extends TestCase
    {
        public function testHasTopicsForum4(): void
        {
            $pdoExtMock = $this->createMock(PDOExt::class);
            
            Database::setConnection($pdoExtMock);
            
            $expectedData = [
                [
                    'a_topic_index' => 1,
                    'a_title' => 'ghjkghjkghjkghjk',
                    'a_posts' => 0,
                    'a_views' => 0,
                    'a_is_pinned' => 0,
                    'a_is_closed' => 0,
                    'a_is_hidden' => 0,
                    'a_starter_index' => 1,
                    'a_starter_name' => 'Administrator',
                    'a_starter_avatar_url' => '',
                    'a_starter_avatar_bgcolor' => '#3f8eca',
                    'a_starter_time' => 1754991150,
                    'a_starter_prefix' => '<span style=\'color:#f44336\'>',
                    'a_starter_suffix' => '</span>',
                    'a_lastpost_index' => 1,
                    'a_lastpost_name' => 'Administrator',
                    'a_lastpost_avatar_url' => '',
                    'a_lastpost_avatar_bgcolor' => '#3f8eca',
                    'a_lastpost_time' => 1754991150,
                    'a_lastpost_prefix' => '<span style=\'color:#f44336\'>',
                    'a_lastpost_suffix' => '</span>',
                    'a_member_last_visit' => 1754991296,
                ],
            ];
            
            
            $pdoExtMock->expects($this->once())
                ->method('select')
                ->willReturn($expectedData);
                
            
            $forumService = ServiceFactory::createForumService();
            $topics = $forumService->getTopicList(4);
            
            // False if something wend wrong
            $this->assertNotFalse($topics);
            
            // Check if there are topics found
            $this->assertGreaterThan(0, count($topics));
            
            // Check if the array is a instance of class Topic
            $this->assertInstanceOf(Topic::class, $topics[0]);
            
            // Check if the data is correctly set
            $this->assertEquals(1, $topics[0]->index);
            
        }
    }