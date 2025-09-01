<?php
    
    //TODO : Main routes in separate files
    
    
    // Index page routes
    
    
    /** @var Router $router */
    $router->add('GET', '/', fn() => new IndexController()->index());
    
    $router->add('GET', '/view/{forumIndex:d}', fn(int $forumIndex) => new IndexController()->view($forumIndex));
    
    
    // Forum routes
    $router->add('GET', '/forum/{forumIndex:d}', fn(int $forumIndex) => new ForumController()->view($forumIndex));
    $router->add('GET', '/forum/view/{forumIndex:d}', fn(int $forumIndex) => new ForumController()->view($forumIndex));
    $router->add('GET', '/forum/newtopic/{forumIndex:d}', fn(int $forumIndex) => new ForumController()->newtopic($forumIndex));
    $router->add('GET', '/forum/recent', fn() => new ForumController()->recent());
    $router->add('GET', '/forum/recent/{page:d}', fn(int $page) => new ForumController()->recent($page));
    $router->add('POST', '/forum/addtopic/', fn() => new ForumController()->addtopic());
    $router->add('GET', '/forum/markallread/', fn() => new ForumController()->getMarkAllRead());
    
    
    // Topic routes
    $router->add('GET', '/topic/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->view($topicIndex));
    $router->add('GET', '/topic/view/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->view($topicIndex));
    $router->add('GET', '/topic/{topicIndex:d}/{page:d}', fn(int $topicIndex, int $page) => new TopicController()->view($topicIndex, $page));
    $router->add('GET', '/topic/view/{topicIndex:d}/{page:d}', fn(int $topicIndex, int $page) => new TopicController()->view($topicIndex, $page));
    $router->add('GET', '/topic/edit/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->edit($topicIndex));
    $router->add('GET', '/topic/move/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->move($topicIndex));
    $router->add('POST', '/topic/domove', fn() => new TopicController()->domove());
    $router->add('POST', '/topic/toggleclose/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->toggleClose($topicIndex));
    $router->add('POST', '/topic/togglepin/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->togglePin($topicIndex));
    $router->add('POST', '/topic/togglehide/{topicIndex:d}', fn(int $topicIndex) => new TopicController()->toggleHide($topicIndex));
    $router->add('POST', '/topic/saveedit', fn(int $topicIndex) => new TopicController()->saveedit());
    $router->add('POST', '/topic/addreply', fn() => new TopicController()->addreply());
    
    
    // Post routes
    $router->add('GET', '/post/edit/{postIndex:d}', fn(int $postIndex) => new PostController()->edit($postIndex));
    $router->add('POST', '/post/update/{postIndex:d}', fn(int $postIndex) => new PostController()->update());
    $router->add('GET', '/post/like/{postIndex:d}', fn(int $postIndex) => new PostController()->like($postIndex));
    $router->add('GET', '/post/toggleHide/{postIndex:d}', fn(int $postIndex) => new PostController()->togglehide($postIndex));
    
    // Private messages
    $router->add('GET', '/inbox', fn() => new ConversationController()->conversationList());
    $router->add('GET', '/inbox/{page:d}', fn(int $page) => new ConversationController()->conversationList($page));
    $router->add('GET', '/conversation/create', fn() => new ConversationController()->getCreateConversationForm());
    $router->add('POST', '/conversation/create', fn() => new ConversationController()->postCreateConversationForm());
    $router->add('GET', '/conversation/view/{conversationIndex:d}', fn(int $conversationIndex) => new ConversationController()->viewConversation($conversationIndex));
    $router->add('POST', '/conversation/addmessage', fn() => new ConversationController()->addMessage());
    
    // Authorization
    $router->add('GET', '/login', fn() => new AuthController()->getLoginForm());
    $router->add('POST', '/login', fn() => new AuthController()->postLoginForm());
    $router->add('GET', '/logout', fn() => new AuthController()->getLogout());
    $router->add('GET', '/lostpassword', fn() => new AuthController()->getLostPasswordForm());
    
    $router->add('GET', '/register', fn() => new RegistrationController()->getRegistrationForm());
    $router->add('POST', '/register', fn() => new RegistrationController()->postRegistrationForm());
    $router->add('GET', '/register/verify/{verificationCode:s}', fn(string $verificationCode) => new RegistrationController()->getUseVerificationCode($verificationCode));
    
    $router->add('GET', '/captcha', fn() => new RegistrationController()->getCaptchaImage());
    $router->add('GET', '/usercp/{userIndex:d}', fn(int $userIndex) => new UserController()->getMemberProfile($userIndex));
    
    // Fast access to create cache
    //TODO : Remove before live
    $router->add('GET', '/createcache', function (): void
    {
        Cache::SaveForumIndex();
        Cache::SavePermissions();
    });