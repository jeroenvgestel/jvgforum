<?php
    
    class MysqlRepository extends Repository
    {
        protected function getDatabaseConnection(): false|PdoExt
        {
            return Database::getConnection();
        }

    }