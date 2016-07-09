<?php

namespace TeamspeakClient\Model;

class Channel
{
    protected $channelId;
    protected $parentId;
    protected $channelOrder;
    protected $name;
    
    protected $clients = [];
    protected $children = [];

    public function __construct($channelStr) {
        preg_match('/cid=\d+/', $channelStr, $matches);
        $cid = reset($matches);
        $this->channelId = (int) substr($cid, 4);
        
        preg_match('/pid=\d+/', $channelStr, $matches);
        $pid = reset($matches);
        $this->parentId = (int) substr($pid, 4);
        
        preg_match('/channel_order=\d+/', $channelStr, $matches);
        $cOrder = reset($matches);
        $this->channelOrder = (int) substr($cOrder, 14);
        
        preg_match('/channel_name=\S+/', $channelStr, $matches);
        $cName = reset($matches);
        $this->name = substr($cName, 13);
        $this->name = str_replace('\s', ' ', $this->name);
    }
    
    public function getChannelId() {
        return $this->channelId;
    }

    public function getParentId() {
        return $this->parentId;
    }

    public function getChannelOrder() {
        return $this->channelOrder;
    }

    public function getName() {
        return $this->name;
    }

    public function setChannelId($channelId) {
        $this->channelId = $channelId;
    }

    public function setParentId($parentId) {
        $this->parentId = $parentId;
    }

    public function setChannelOrder($channelOrder) {
        $this->channelOrder = $channelOrder;
    }

    public function setName($name) {
        $this->name = $name;
    }
    
    public function addChild(Channel $channel)
    {
        $this->children[] = $channel;
    }
    
    public function getChildren()
    {
        return $this->children;
    }

    public function addClient(Client $client)
    {
        $this->clients[] = $client;
    }
    
    public function getClients()
    {
        return $this->clients;
    }
}
