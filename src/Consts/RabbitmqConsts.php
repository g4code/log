<?php

namespace G4\Log\Consts;

class RabbitmqConsts
{
    const SOURCE                = 'source';
    
    const CORE                  = 'core';
    const CORE_SENDER           = 'user_id';
    const CORE_RECEIVER         = 'actor_user_id';    
    
    const BACKFIRE              = 'backfire';
    const BACKFIRE_SENDER       = 'user';
    const BACKFIRE_RECEIVER     = 'animated_user';
    
    const CHAT_APP              = 'chat-app';
    const CHAT_APP_SENDER       = 'sender';
    const CHAT_APP_RECEIVER     = 'receiver';

    const SPAM_SCAM              = 'spam-scam';
    const SPAM_SCAM_SENDER       = 'sender';
    const SPAM_SCAM_RECEIVER     = 'receiver';
}