<?php
/**
 * Debug Class
 *
 * Handle all debug actions
 *
 * @package    G4
 * @author     Dejan Samardzija, samardzija.dejan@gmail.com
 * @copyright  Little Genius Studio www.littlegeniusstudio.com All rights reserved
 * @version    1.0
 */

namespace G4\Log;

use G4\Buffer\Buffer;
use G4\DI\Container as DI;

class Debug
{
    /**
     * Errors/Exceptions styling
     * @var string
     */
    private static $cssParameters = [
        'position: relative',
        'background: #ffdfdf',
        'border: 2px solid #ff5050',
        'margin: 5px auto',
        'padding: 10px',
        'min-width: 720px',
        'width: 50%',
        'font: normal 12px "Verdana", monospaced',
        'overflow: auto',
        'z-index: 100',
        'clear: both'
    ];

    /**
     * Log file extension
     * @var string
     */
    private static $_extenstion = '.log';

    /**
     * Flag is ajax or cli request
     * @var bool
     */
    private static $_isAjaxOrCli;

    /**
     * Default error handler
     *
     * @param  int    $errno   - error number
     * @param  string $errstr  - error string
     * @param  string $errfile - filename where error occured
     * @param  int    $errline - line number in the filename where error occured
     * @return void
     */
    public static function handlerError($errno, $errstr, $errfile, $errline)
    {
        $bad = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

        // set log file name
        switch ($errno) {
            case E_ERROR:             $fn = "error";                 break;
            case E_WARNING:           $fn = "warning";               break;
            case E_PARSE:             $fn = "parse";                 break;
            case E_NOTICE:            $fn = "notice";                break;
            case E_CORE_ERROR:        $fn = "core_error";            break;
            case E_CORE_WARNING:      $fn = "core_warning";          break;
            case E_COMPILE_ERROR:     $fn = "compile_error";         break;
            case E_COMPILE_WARNING:   $fn = "compile_warning";       break;
            case E_USER_ERROR:        $fn = "user_error";            break;
            case E_USER_WARNING:      $fn = "user_warning";          break;
            case E_USER_NOTICE:       $fn = "user_notice";           break;
            case E_STRICT:            $fn = "strict";                break;
            case E_RECOVERABLE_ERROR: $fn = "recoverable_error";     break;
            case E_DEPRECATED:        $fn = "deprecated";            break;
            case E_USER_DEPRECATED:   $fn = "user_deprecated";       break;
            case E_ALL:               $fn = "all";                   break;
            default:                  $fn = "__{$errno}__undefined"; break;
        }

        if(defined('PATH_ROOT')) {
            $errfile = str_replace(realpath(PATH_ROOT), '', $errfile);
        }

        $err_msg = strtoupper($fn) . ": {$errstr}\nLINE: {$errline}\nFILE: {$errfile}";

        // With this setup errors are displayed according to the error_reporting() setting
        // but all errors are logged to file, regardles of
        if(defined('DEBUG') && DEBUG && error_reporting() & $errno) {
            echo self::_skipFormating()
                ? $err_msg . PHP_EOL
                : sprintf("<pre>\n<p style='%s'>\n%s\n</p>\n</pre>\n\n", self::getFormattedCss(), $err_msg);
        }

        // all errors are logged
        if(!empty($fn)) {
            self::_writeLog($fn . self::$_extenstion, $err_msg);
            self::_writeLogJson($fn, $errstr, $errno, $errline, $errfile, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        // On production setup, if bad errors occurs, send mail with parsed exception and terminate the script
        // DISABLED FOR NOW SINCE IT'S KILLING US AT THE MOMENT!!!!!
        // SWITCH TO EVENTS!!!!!
//         if(in_array($errno, $bad)) {
//             if(defined('DEBUG') && !DEBUG) {
//                 try {
//                     throw new \Exception($errstr);
//                 } catch (\Exception $e) {
//                     $msg = self::_parseException($e);
//                 }

//                 defined('EMAIL_DEBUG')
//                 && defined('EMAIL_DEBUG_FROM')
//                 && @mail (EMAIL_DEBUG, "PHP ERROR: {$errstr}", $msg, 'From: ' . EMAIL_DEBUG_FROM);
//             }
//             exit(1);
//         }
    }

    /**
     * Handle exceptions
     *
     * @param \Exception $e
     * @param boolean $print
     * @return boolean
     */
    public static function handlerException(\Exception $e, $print = true)
    {
        $file = 'exception' . self::$_extenstion;

        $msg = self::_parseException($e, self::_skipFormating());

        self::_writeLog($file, $msg);
        self::_writeLogJson(get_class($e), $e->getMessage(), $e->getCode(), $e->getLine(), $e->getFile(), $e->getTrace());

        return $print ? print($msg) : true;
    }

    /**
     * @return string
     */
    private static function getFormattedCss()
    {
        return implode('; ', self::$cssParameters);
    }

    private static function _writeLogJson($type, $msg, $code, $line, $file, $trace)
    {
        $fullError = array(
            'type'     => $type,
            'message'  => $msg,
            'code'     => $code,
            'line'     => $line,
            'file'     => $file,
            'trace'    => $trace,
            'datetime' => date('Y-m-d H:i:s'),
            'tz'       => date_default_timezone_get(),
            'context'  => self::formatRequestData(false),
        );

        return self::_writeLog('____json__' . $type, json_encode($fullError), false);
    }

    /**
     * Error handler for fatal errors
     * @return void
     */
    public static function handlerShutdown()
    {
        $error = error_get_last();
        if($error !== NULL) {
            self::handlerError($error['type'], '[SHUTDOWN] ' . $error['message'], $error['file'], $error['line']);
        }
    }

    /**
     * Write log with buffer support
     * @param  string $filename
     * @param  string $msg
     * @return void
     */
    private static function _writeLog($filename, $msg, $addTime = true)
    {
        // preppend details
        if($addTime) {
            $msg = self::formatHeaderWithTime() . $msg;
        }

        if(DI::has('BufferOptions')) {

            $options = DI::get('BufferConnectionOptions');

            $callback = function($data) use ($filename) {
                Writer::writeLogVerbose($filename, implode("\n\n", $data) . "\n\n");
            };

            // just to be on safe side, set max size to 500, that is reasonable number
            $maxSize = 500;

            $size = isset($options['size']) && is_int($options['size']) && $options['size'] > 0 && $options['size'] < $maxSize
                ? $options['size']
                : $maxSize;

            $buffer = new Buffer($filename, Buffer::ADAPTER_REDIS, $size, $callback, $options);
            $buffer->add($msg);

        } else {
            Writer::writeLogVerbose($filename, $msg);
        }
    }

    /**
     * Parses exceptions into human readable format + html styling
     *
     * @param  Exception $e          Exception object that's being parsed
     * @param  bool      $plain_text flag to return formated exception, or plain text
     *
     * @todo: finish plain text implemntation
     *
     * @return string
     */
    private static function _parseException(\Exception $e, $plain_text = false)
    {
        $exMsg   = $e->getMessage();
        $exCode  = $e->getCode();
        $exLine  = $e->getLine();
        $exFile  = basename($e->getFile());
        $exTrace = $e->getTrace();

        $trace = '';
        foreach ($exTrace as $key => $row) {
            $trace .= '<span class="traceLine">#' . ($key++) . ' ';

            if (!empty($row['function'])) {
                $trace .= "<b>";
                if (!empty($row['class'])) {
                    $trace .= $row['class'] . $row['type'];
                }

                $trace .= "{$row['function']}</b>()";
            }

            if (!empty($row['file'])) {
                $trace .= " | LINE: <b>{$row['line']}</b> | FILE: <u>" . basename($row['file']) . '</u>';
            }

            $trace .= "</span>\n";
        }

        $msg = "<em style='font-size:larger;'>{$exMsg}</em> (code: {$exCode})<br />\nLINE: <b>{$exLine}</b>\nFILE: <u>{$exFile}</u>";

        $parsed = sprintf("<pre>\n<p style='%s'>\n<strong>EXCEPTION:</strong><br />%s\n%s\n</p>\n</pre>\n\n", self::getFormattedCss(), $msg, $trace);

        return $plain_text ? str_replace(array("\t"), '', strip_tags($parsed)) : $parsed;
    }

    /**
     * Returns all request data formated into string
     * @return string
     */
    public static function formatRequestData($asString = true)
    {
        $fromServer = array(
            'REQUEST_URI',
            'REQUEST_METHOD',
            'HTTP_REFERER',
            'QUERY_STRING',
            'HTTP_USER_AGENT',
            'REMOTE_ADDR',
        );

        if($asString) {
            $s = "\n\n";
            $s .= isset($_REQUEST)? "REQUEST: " . print_r($_REQUEST, true) . PHP_EOL   : '';
            foreach ($fromServer as $item) {
                $s .= isset($_SERVER[$item]) ? "{$item}: {$_SERVER[$item]}\n" : '';
            }
        } else {
            $s = array(
                'REQUEST' => $_REQUEST,
                'SERVER'  => $_SERVER,
            );
        }

        return $s;
    }

    /**
     * Returns full date and time with separating characters
     * @return string
     */
    public static function formatHeaderWithTime()
    {
        $date = date("Y-m-d H:i:s");
        return PHP_EOL . str_repeat('-', 10) . " {$date} " . str_repeat('-', 100) . PHP_EOL;
    }

    /**
     * Check if request is ajax or cli and skip formating
     *
     * @return bool
     */
    private static function _skipFormating()
    {
        return false;

        if(null !== self::$_isAjaxOrCli) {
            return self::$_isAjaxOrCli;
        }

        self::$_isAjaxOrCli =
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            || (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));

        return self::$_isAjaxOrCli;
    }
}