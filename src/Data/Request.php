<?php

namespace G4\Log\Data;

class Request extends RequestResponseAbstarct
{

    /**
     * @var array
     */
    private $paramsToObfuscate;


    public function __construct()
    {
        $this->paramsToObfuscate = [];
    }

    /**
     * @param array $paramsToObfuscate
     * @return \G4\Log\Data\Request
     */
    public function setParamsToObfuscate(array $paramsToObfuscate)
    {
        $this->paramsToObfuscate = $paramsToObfuscate;
        return $this;
    }

    /**
     * @return array
     */
    public function getRawData()
    {
        return array_merge([
            'id'                        => $this->getId(),
            'timestamp'                 => $this->getJsTimestamp(),
            'datetime'                  => \date('Y-m-d H:i:s'),
            'ip'                        => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'cli',
            'module'                    => \strtolower($this->getApplication()->getAppNamespace()),
            'service'                   => \strtolower($this->getApplication()->getRequest()->getResourceName()),
            'method'                    => \strtolower($this->getApplication()->getRequest()->getMethod()),
            'params'                    => \json_encode($this->obfuscateParams($this->getApplication()->getRequest()->getParams())),
            'hostname'                  => \gethostname(),
            'app_key'                   => $this->getApplication()->getRequest()->getParam('X-ND-AppKey') ?: null,
            'app_token'                 => $this->getApplication()->getRequest()->getParam('X-ND-AppToken') ?: null,
            'authentication'            => $this->getApplication()->getRequest()->getParam('X-ND-Authentication') ?: null,
            'client_ip'                 => $this->getClientIp(),
            'app_name'                  => $this->getAppName(),
            'server_http_host'          => isset($_SERVER['HTTP_HOST'])       ? $_SERVER['HTTP_HOST']       : '',
            'server_request_uri'        => isset($_SERVER['REQUEST_URI'])     ? $_SERVER['REQUEST_URI']     : '',
            'server_request_method'     => isset($_SERVER['REQUEST_METHOD'])  ? $_SERVER['REQUEST_METHOD']  : '',
            'server_http_user_agent'    => isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '',
            'headers'                   => \json_encode($this->getXNDParameters()),
            'uuid'                      => $this->getUuid(),
            'php_version'               => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
        ], $this->getAdditionLogInformation());
    }

    /**
     *
     * @param array $params
     * @return array
     */
    private function obfuscateParams(array $params)
    {
        \parse_str(\trim(\file_get_contents('php://input')), $rawBodyParams);

        foreach($this->paramsToObfuscate as $key) {
            if (isset($params[$key])) {
                $params[$key] = '*****';
            }
            if (is_array($rawBodyParams) && \array_key_exists($key, $rawBodyParams)) {
                $rawBodyParams[$key] = '_obfuscated_';
            }
        }

        $params['raw_body'] = \http_build_query($rawBodyParams);
        return $params;
    }
}
