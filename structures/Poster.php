<?php
    
    class Poster
    {
        public int $index;
        public string $name;
        public string $url;
        public string $date;
        public string $avatarUrl;
        public string $avatarBgColor;
        public string $prefix;
        public string $suffix;
        public string $groupName;
        public int $postCount;
        public int $timeStamp;
        
        
        /**
         * Sets the data from an array
         * @param string $prefix
         * @param array $data
         * @return void
         */
        public function setData(string $prefix, array $data): void
        {
            $this->index = $data[$prefix . '_index'] ?? 0;
            $this->name = $data[$prefix . '_name'] ?? '';
            $this->url = URL . 'usercp/view/' . $this->index;
            $this->avatarUrl = $data[$prefix . '_avatar_url'] ?? '';
            $this->avatarBgColor = $data[$prefix . '_avatar_bg_color'] ?? '';
            $this->prefix = $data[$prefix . '_prefix'] ?? '';
            $this->suffix = $data[$prefix . '_suffix'] ?? '';
            $this->groupName = $data[$prefix . '_group_name'] ?? '';
            
            if(isset($data[$prefix . '_time']))
            {
                $this->date = Utils::processTimestamp($data[$prefix . '_time']);
                $this->timeStamp = $data[$prefix . '_time'];
            }
            
            if(isset($data[$prefix . '_avatar_bgcolor']))
                $this->avatarBgColor = 'style="fill: ' . $data[$prefix . '_avatar_bgcolor'] . '" ';
            
        }
    }
