<?php

namespace G4\Log\Error;

class Presenter
{

    /**
     * @var array
     */
    private $css = [
        'position: relative',
        'background: #ffdfdf',
        'border: 2px solid #ff5050',
        'margin: 5px auto',
        'padding: 10px',
        'min-width: 720px',
        'width: 50%',
        'font: normal 12px Verdana, monospaced',
        'overflow: auto',
        'z-index: 100',
        'clear: both'
    ];

    /**
     * @var ErrorData
     */
    private $data;


    public function display()
    {
        echo $this->isCli()
            ? $this->data->getDataAsString()
            : $this->getDataAsHtml();
    }

    /**
     * @param ErrorData $data
     * @return \G4\Profiler\Presenter
     */
    public function setData(ErrorData $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string
     */
    private function getFormattedCss()
    {
        return join('; ', $this->css);
    }

    /**
     * @return string
     */
    private function getDataAsHtml()
    {
        return sprintf($this->getHtml(), $this->getFormattedCss(), $this->data->getDataAsString());
    }

    /**
     * @return string
     */
    private function getHtml()
    {
        return \join(PHP_EOL, [
            '<pre>',
                '<p style="%s">',
                    '%s',
                '</p>',
            '</pre>',
        ]) . PHP_EOL . PHP_EOL;
    }

    /**
     * @return bool
     */
    private function isCli()
    {
        return \php_sapi_name() == 'cli'
            && empty($_SERVER['REMOTE_ADDR']);
    }
}
