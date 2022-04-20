<?php

namespace G4\Log\Mapper;

use G4\Log\Consts\RabbitmqConsts;

class RabbitmqMessages implements MessagesMapInterface
{
    /** @var array */
    private $allowedSources;

    private $sourceAllowed;

    private $message;
    private $message_decoded;
    private $source;
    private $userSender;
    private $userReceiver;

    public function __construct($message, $allowedSources)
    {
        $this->allowedSources   = $allowedSources;
        $this->message          = $message;
        $this->decodeMessage();
    }

    public function isSourceAllowed()
    {
        return $this->sourceAllowed;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getUserSender()
    {
        return $this->userSender;
    }

    public function getUserReceiver()
    {
        return $this->userReceiver;
    }

    public function getMessage()
    {
        return $this->message;
    }

    private function decodeMessage()
    {
        $this->message_decoded = isset($this->message) ? json_decode($this->message, true) : null;

        $this->sourceAllowed = true;

        if(!empty($this->message_decoded)) {
            switch (true) {
                case $this->isFromCoreAndAllowed():
                    $this->coreMapping();
                    break;
                case $this->isFromBackfireAndAllowed():
                    $this->backfireMapping();
                    break;
                case $this->isFromChatAppAndAllowed():
                    $this->chatAppMapping();
                    break;
                default:
                    $this->sourceAllowed = false;
                    break;
            }
        }
    }

    private function inAllowedSources($source)
    {
        return in_array($source, $this->allowedSources);
    }

    private function sourceMatch($source)
    {
        return isset($this->message_decoded[RabbitmqConsts::SOURCE]) && $this->message_decoded[RabbitmqConsts::SOURCE] === $source;
    }
    
    private function isFromCoreAndAllowed()
    {
        return $this->inAllowedSources(RabbitmqConsts::CORE) && $this->sourceMatch(RabbitmqConsts::CORE);
    }    
    
    private function isFromBackfireAndAllowed()
    {
        return $this->inAllowedSources(RabbitmqConsts::BACKFIRE) && $this->sourceMatch(RabbitmqConsts::BACKFIRE);
    }

    private function isFromChatAppAndAllowed()
    {
        return $this->inAllowedSources(RabbitmqConsts::CHAT_APP) && $this->sourceMatch(RabbitmqConsts::CHAT_APP);
    }
    
    private function coreMapping()
    {
        $this->source           = RabbitmqConsts::CORE;
        $this->userSender       = $this->message_decoded[RabbitmqConsts::CORE_SENDER];
        $this->userReceiver     = $this->message_decoded[RabbitmqConsts::CORE_RECEIVER];
    }

    private function backfireMapping()
    {
        $this->source           = RabbitmqConsts::BACKFIRE;
        $this->userSender       = $this->message_decoded[RabbitmqConsts::BACKFIRE_SENDER];
        $this->userReceiver     = $this->message_decoded[RabbitmqConsts::BACKFIRE_RECEIVER];
    }
    
    private function chatAppMapping()
    {
        $this->source           = RabbitmqConsts::CHAT_APP;
        $this->userSender       = $this->message_decoded[RabbitmqConsts::CHAT_APP_SENDER];
        $this->userReceiver     = $this->message_decoded[RabbitmqConsts::CHAT_APP_RECEIVER];
    }
}