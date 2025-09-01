<?php
    
    class MysqlModel extends Model
    {
        protected function getDatabaseConnection(): false|PdoExt
        {
            return Database::getConnection();
        }

    }