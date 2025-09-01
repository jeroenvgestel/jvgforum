<?php

    class Database
    {
        protected static ?PdoExt $db = null;

        /**
         * Connects to the database
         * @return void
         */
        private static function connect(): void
        {
            try
            {
                self::$db = new PdoExt('mysql:host=' . Config::DBCON_URL . ';dbname=' . Config::DBCON_DATABASE, Config::DBCON_USER, Config::DBCON_PASS);
            }
            catch (PDOException $e)
            {
                error_log($e->getMessage());
                self::$db = null;
            }
        }
        
        
        /**
         * Gets a database connection if it exists, or connects if it doesn't
         * @return false|PdoExt
         */
        public static function getConnection(): false|PdoExt
        {
            if(!self::$db)
                self::connect();

            if (!self::$db)
                return false;

            if(self::$db->isConnected)
                return self::$db;

            else
                return false;
        }
        
        public static function setConnection(PdoExt $db): void
        {
            self::$db = $db;
            $db->isConnected = true;
        }
    }