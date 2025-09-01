<?php
    
    use Random\RandomException;
    
    const MINUTE = 60;
    const HOUR = MINUTE * 60;
    const DAY = HOUR * 24;

    class Utils
    {
        
        /**
         * Checks if a number is a positive number
         * @param mixed $str
         * @return bool
         */
        public static function isNumeric(mixed $str): bool
        {
            return preg_match('/^[0-9]+$/', $str);
        }
        
        
        /**
         * Processes a timestamp into a readable string 3 days ago, 4 hours ago, etc..
         * @param int $timestamp
         * @return string
         */
        public static function processTimestamp(int $timestamp): string
        {
            if($timestamp == 0)
                return 'Never';

            $now = time();
            $diff = $now - $timestamp;

            if(date('Y', $now) != date('Y', $timestamp))
                return date('M n, Y', $timestamp);

            if($diff >= (DAY * 3))
                return date('M n', $timestamp);

            if($diff >= DAY)
            {
                return floor($diff / DAY) . ' days ago';
            }

            if($diff >= HOUR)
            {
                return floor($diff / HOUR) . ' hours ago';
            }

            if($diff >= MINUTE)
            {
                return ceil($diff / MINUTE) . ' minutes ago';
            }

            return 'Just now';
        }
        
        
        /**
         * Generates a random string to use for codes
         * @param int $length
         * @return string
         */
        public static function generateSecureRandomKey(int $length = 32): string
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $charactersLength = strlen($characters);
            $randomKey = '';
            
            for ($i = 0; $i < $length; $i++) 
            {
                $randomIndex = rand(0, $charactersLength - 1);
                
                try
                {
                    $randomIndex = random_int(0, $charactersLength - 1);
                }
                catch (RandomException $e)
                {
                    error_log('RandomException: ' . $e->getMessage());
                }
                
                $randomKey .= $characters[$randomIndex];
            }
            
            return $randomKey;
        }
        
        
        /**
         * Transforms a number to a more human readable string (2.3k, 15m)
         * @param int $number
         * @return string
         */
        public static function readableNumber(int $number): string
        {
            if($number < 1000)
                return $number;

            if($number < 1_000_000)
                return round($number / 1000, 1) . 'k';

            return round($number / 1_000_000, 2) . 'm';
        }
        
        
        /**
         * Send a simple email to an email address
         * TODO: replace with something more robust
         * @param string $to Email Address
         * @param string $subject
         * @param string $message
         * @return bool
         */
        public static function SendMail(string $to, string $subject, string $message): bool
        {
            $forumEmail = Config::FORUM_EMAIL;
            $headers = "From: $forumEmail\r\n";
            $headers .= "Reply-To: $forumEmail\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

            return mail($to, $subject, $message, $headers);
        }
        
        
        /**
         * Gets a random background color for the default avatar
         * @return string
         */
        public static function getRandomAvatarColor(): string
        {
            $avatarColors = ["#4A90E2","#50E3C2","#B8E986","#F5A623","#F8E71C","#D0021B","#BD10E0","#9013FE","#8B572A","#7ED321","#417505","#F78DA7"];
            
            $index = 0;
            try
            {
                random_int(0, count($avatarColors) - 1);
            }
            catch(Exception $e)
            {
                error_log($e->getMessage());
            }
            
            return $avatarColors[$index];
        }
    }
