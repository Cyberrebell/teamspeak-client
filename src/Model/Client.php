<?php

namespace TeamspeakClient\Model;

class Client
{
    const CLIENT_TYPE_CLIENT = 0;
    const CLIENT_TYPE_QUERY = 1;
    
    protected $clientId;
    protected $channelId;
    protected $clientType;
    protected $name;
    
    public function __construct($clientStr) {
        preg_match('/clid=\d+/', $clientStr, $matches);
        $clid = reset($matches);
        $this->clientId = (int) substr($clid, 5);
        
        preg_match('/cid=\d+/', $clientStr, $matches);
        $cid = reset($matches);
        $this->channelId = (int) substr($cid, 4);
        
        preg_match('/client_type=\d+/', $clientStr, $matches);
        $clientType = reset($matches);
        $this->clientType = (int) substr($clientType, 12);
        
        preg_match('/client_nickname=\S+/', $clientStr, $matches);
        $cName = reset($matches);
        $this->name = substr($cName, 16);
        $this->name = str_replace('\s', ' ', $this->name);
    }
    
    public function getClientId() {
        return $this->clientId;
    }

    public function getChannelId() {
        return $this->channelId;
    }

    public function getClientType() {
        return $this->clientType;
    }

    public function getName() {
        return $this->name;
    }

    public function setClientId($clientId) {
        $this->clientId = $clientId;
    }

    public function setChannelId($channelId) {
        $this->channelId = $channelId;
    }

    public function setClientType($clientType) {
        $this->clientType = $clientType;
    }

    public function setName($name) {
        $this->name = $name;
    }
}
