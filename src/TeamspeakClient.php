<?php

namespace TeamspeakClient;

use TeamspeakClient\Model\Channel;
use TeamspeakClient\Model\Client;

class TeamspeakClient
{
    const BUFFER_LENGTH = 1448;
    
    protected $host;
    protected $port;
    protected $connection;
    protected $errorMessage;

    public function __construct($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }
    
    public function connect()
    {
        $this->connection = @fsockopen($this->host, $this->port, $errno, $errstr, 3);
        if ($this->connection) {
            stream_set_timeout($this->connection, 1);
            $this->read();  //skip initial messages
            $this->read();
            $this->request('use 1');    //use first virtual server by default
            return true;
        } else {
            $this->errorMessage = $errstr . ' (' . $errno . ')';
            return false;
        }
    }
    
    public function disconnect()
    {
        fclose($this->connection);
    }
    
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
    
    public function write($msg)
    {
        fwrite($this->connection, $msg);
    }
    
    public function read()
    {
        return fread($this->connection, self::BUFFER_LENGTH);
    }
    
    public function readAll()
    {
        $response = '';
        while ($buffer = $this->read()) {
            $response .= $buffer;
            if (strlen($buffer) < self::BUFFER_LENGTH) {
                break;
            }
        }
        return $response;
    }
    
    public function request($command)
    {
        $this->write($command . PHP_EOL);
        return $this->readAll();
    }
    
    public function query($command)
    {
        $result = $this->request($command);
        $successMsg = $this->readAll();
        if (!strstr($successMsg, 'error id=0 msg=ok')) {
            return false;
        }
        return $result;
    }

    public function login($username, $password)
    {
        $this->request('login ' . $username . ' ' . $password);
    }
    
    public function getChannels()
    {
        $channelListStr = $this->query('channellist');
        $channelStrs = explode('|', $channelListStr);
        $channels = [];
        foreach ($channelStrs as $channelStr) {
            $channel = new Channel($channelStr);
            $channels[$channel->getChannelId()] = $channel;
        }
        foreach ($channels as $cid => $channel) {
            if ($channel->getParentId() != 0) {
                $channels[$channel->getParentId()]->addChild($channel);
            }
        }
        foreach ($channels as $cid => $channel) {
            if ($channel->getParentId() != 0) {
                unset($channels[$cid]);
            }
        }
        return $channels;
    }
    
    public function getClients()
    {
        $clientListStr = $this->query('clientlist');
        $clientStrs = explode('|', $clientListStr);
        $clients = [];
        foreach ($clientStrs as $clientStr) {
            $client = new Client($clientStr);
            if ($client->getClientType() == Client::CLIENT_TYPE_CLIENT && $client->getClientId() != 0) {
                $clients[$client->getClientId()] = $client;
            }
        }
        return $clients;
    }
    
    public function getChannelsWithClients()
    {
        $channels = $this->getChannels();
        $clients = $this->getClients();
        /* @var $channel Channel */
        foreach ($channels as $channel) {
            foreach ($clients as $clientId => $client) {
                if ($client->getChannelId() == $channel->getChannelId()) {
                    $channel->addClient($client);
                    unset($clients[$clientId]);
                }
            }
        }
        return $channels;
    }
    
    public function getServerInfo()
    {
        $infoStr = $this->request('serverinfo');
        $settings = explode(' ', $infoStr);
        $result = [];
        foreach ($settings as $setting) {
            $delimiter = strpos($setting, '=');
            if ($delimiter) {
                $result[substr($setting, 0, $delimiter)] = substr($setting, $delimiter + 1);
            }
        }
        return $result;
    }
}
