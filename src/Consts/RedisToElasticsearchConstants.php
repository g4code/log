<?php

namespace G4\Log\Consts;

class RedisToElasticsearchConstants
{
    //es versions

    const DEFAULT_ES            = 'defaultES';
    const ES6                   = 'es6';
    const ES7                   = 'es7';
    const ES8                   = 'es8';

    //metha params
    const __METHOD              = '__method';
    const _INDEX                = '_index';
    const _TYPE                 = '_type';
    const INDEX_TYPE            = 'index_type';
    const _ID                   = '_id';
    const _DOC                  = '_doc';

    //data params
    const ID                    = 'id';

    //method options
    const METHOD_INDEX          = 'index';
    const METHOD_CREATE         = 'create';
    const METHOD_UPDATE         = 'update';
    const METHOD_UPDATE_WRAP    = 'doc';
    const METHOD_DELETE         = 'delete';
}