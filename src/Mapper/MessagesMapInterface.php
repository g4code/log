<?php

namespace G4\Log\Mapper;

interface MessagesMapInterface
{
    public function isSourceAllowed();
    
    public function getSource();

    public function getUserSender();
    
    public function getUserReceiver();
    
    public function getMessage();
}