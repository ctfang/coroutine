<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/6
 * Time: 17:23
 */

namespace Utopia\Services;


use Noodlehaus\AbstractConfig;

class ConfigService extends AbstractConfig
{
    protected $data = [];

    public function __construct($path)
    {
        if (is_dir($path)) {
            $paths = glob($path.'/*.php');
            foreach ($paths as $path) {
                $array = include $path;
                if ($array) {
                    $this->data = array_replace_recursive($this->data, $array);
                }
            }
        }

        parent::__construct($this->data);
    }
}