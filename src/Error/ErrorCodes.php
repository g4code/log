<?php

namespace G4\Log\Error;

class ErrorCodes
{

    /**
     * @var array
     */
    private static $map = [
        E_ERROR             => "error",
        E_WARNING           => "warning",
        E_PARSE             => "parse",
        E_NOTICE            => "notice",
        E_CORE_ERROR        => "core_error",
        E_CORE_WARNING      => "core_warning",
        E_COMPILE_ERROR     => "compile_error",
        E_COMPILE_WARNING   => "compile_warning",
        E_USER_ERROR        => "user_error",
        E_USER_WARNING      => "user_warning",
        E_USER_NOTICE       => "user_notice",
        E_STRICT            => "strict",
        E_RECOVERABLE_ERROR => "recoverable_error",
        E_DEPRECATED        => "deprecated",
        E_USER_DEPRECATED   => "user_deprecated",
        E_ALL               => "all",
    ];

    /**
     * @param $errno
     * @return string
     */
    public static function getName($errno)
    {
        return isset(self::$map[$errno])
            ? self::$map[$errno]
            : "__{$errno}__undefined";
    }
}
