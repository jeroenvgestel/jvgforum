<?php
    
    class PaginationInfo
    {
        public string $url;
        public int $pageCount;
        public int $currentPage;
        
        public function __construct(string $url, int $pageCount, int $currentPage)
        {
            $this->url = $url;
            $this->pageCount = $pageCount;
            $this->currentPage = $currentPage;
        }
    }