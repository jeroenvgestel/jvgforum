<?php
    
    declare(strict_types=1);
    
    use PHPUnit\Framework\TestCase;
    
    class PostTest extends TestCase
    {
        public function testCanAddReplyToTopic()
        {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
            
            $postRepository = new PostRepository();
            $result = $postRepository->addReplyToTopic(1, 4, "Test Reply");
            
            $this->assertNotFalse($result);
            
            $this->assertIsNumeric($result);
        }
    }
