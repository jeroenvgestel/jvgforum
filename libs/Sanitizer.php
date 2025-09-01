<?php

    use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
    use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

    class Sanitizer
    {
        private static ?HtmlSanitizer $sanitizer = null;
        
        /**
         * Initialization only done when we need it
         * @return void
         */
        public static function Init(): void
        {
            self::$sanitizer = new HtmlSanitizer(
                new HtmlSanitizerConfig()->allowSafeElements()
            );
        }
        
        /**
         * Sanitizes a string on its html content
         * @param string $input
         * @return string sanitizedString
         */
        public static function Sanitize(string $input): string
        {
            if(self::$sanitizer == null)
                self::Init();

            return self::$sanitizer->sanitize($input);
        }

    }