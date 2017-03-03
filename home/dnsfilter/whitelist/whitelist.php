<?php

class whitelist
{
    private $location = __DIR__.'/whitelist';
    private $locationDefault = __DIR__.'/default.whitelist';

    public function load()
    {
        return file_get_contents($this->location);
    }

    public function loadDefault()
    {
        return file_get_contents($this->locationDefault);
    }

    public function save($whiteList)
    {
        $whiteListContent = $this->sanitizeWhiteList($whiteList);
        file_put_contents($this->location, $whiteListContent);
        exec('sudo /usr/local/bin/dnsmasqconfig');

        return $whiteListContent;
    }

    private function sanitizeWhiteList($whiteList)
    {
        $whiteListArr = explode("\n", $whiteList);
        foreach($whiteListArr as $key => $line) {
            $line = trim($line);
            if (strlen($line) === 0 || substr($line,0,1) === '#') continue; //comment or empty

            $line = preg_replace('#^https?://#', '', $line);
            $line = preg_replace('#^www\.#', '', $line);
            $line = preg_replace('#^[/ ]*#', '', $line);
            $line = preg_replace('#/.*$#', '', $line);
            $line = preg_replace('/[^\w#.-]/', '', $line);
            $whiteListArr[$key] = $line;
        }
        return implode("\n", $whiteListArr);
    }
}