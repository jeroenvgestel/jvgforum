<?php

    // Complete this config file and rename it to config.php
    
    const URL = ''; // Forum base url
    const BASEPATH = ''; // forum root directory
    const MODEL_PATH = 'models/';
    const CONTROLLER_PATH = 'controllers/';
    const SERVICE_PATH = 'services/';
    const VIEW_PATH = 'views/';
    const STRUCTURES_PATH = 'models/';
    const DEFAULT_TITLE = ''; // Page Title
    

    class Config
    {
        public const string DBCON_URL = ''; // Database IP here
        public const string DBCON_USER = ''; // Database Username
        public const string DBCON_PASS = ''; // Database Password
        public const string DBCON_DATABASE = ''; // Database

        public const string SESSION_NAME = 'wf';

        public const string COOKIE_NAME = 'wf';
        public const int COOKIE_KEY_SIZE = 32;
        public const int COOKIE_EXPIRE_TIME = (60*60*24*365);
        public const string COOKIE_HMAC_KEY = ''; // Random secure key here

        public const int TOPICS_PER_PAGE = 20;
        public const int POSTS_PER_PAGE = 10;
        public const int RECENT_CONTENT_TIME = (60*60*24*7*52);
        public const int ALLOWED_EDIT_TIME = (60*60*7);
        public const int PM_ALLOWED_RECEIPIENTS = 10;
        public const int CAPTCHA_KEY_LENGTH = 5;
        
        public const string FORUM_EMAIL = ""; // Forum reply to email address
        public const int REGISTRATION_VERIFICATION_KEY_SIZE = 10;
        public const int USERGROUP_MEMBERS = 3;
        public const bool MEMBER_CAN_HIDE_OWN_POST = false;

        public const bool LOCALTEST = true;
    }
