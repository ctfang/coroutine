<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/8/10
 * Time: 14:15
 */

namespace Utopia\Helper;

use Composer\Autoload\ClassLoader;
use Utopia\Application;

/**
 * 目录工具
 *
 * @package Utopia\Helper
 */
class DirectoryHelper
{
    /** @var array 命名空间=》目录 */
    private $dirBindNameSpace = [];

    /** @var string 遍历后缀 */
    private $extensions = '.php';

    /** @var ClassLoader */
    private $loader;

    public function __construct($namespaces)
    {
        $this->setLoader(Application::getLoader());
        $this->setScanNamespace($namespaces);
    }

    /**
     * 设置遍历的目录
     *
     * @param $directory
     * @param $nameSpace
     */
    public function setScanDirectory($directory, $nameSpace)
    {
        $this->dirBindNameSpace[$directory] = $nameSpace;
    }

    /**
     * 设置搜索文件的扩展名
     *
     * @param $extensions
     */
    public function setExtensions($extensions)
    {
        $this->extensions = $extensions;
    }

    /**
     * 遍历目录下所有类
     *
     * @return \Generator
     */
    public function scanClass()
    {
        foreach ($this->dirBindNameSpace as $directory => $nameSpace) {
            foreach ($this->scanNameSpace($directory, $nameSpace) as $class) {
                yield $class;
            }
        }
    }

    /**
     * 遍历命名空间
     *
     * @param $directory
     * @param $nameSpace
     * @return \Generator
     */
    private function scanNameSpace($directory, $nameSpace)
    {
        if ($dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                } elseif (is_dir($directory.DIRECTORY_SEPARATOR.$file)) {
                    foreach ($this->scanNameSpace(
                        $directory.DIRECTORY_SEPARATOR.$file,
                        $nameSpace.$file."\\"
                    ) as $arrValue) {
                        yield $arrValue;
                    }
                } elseif (substr($file, -4) == $this->extensions) {
                    yield $nameSpace.pathinfo($file, PATHINFO_FILENAME);
                }
            }
            closedir($dh);
        }
    }

    /**
     * 设置类加载器
     *
     * @param ClassLoader $loader
     */
    public function setLoader(ClassLoader $loader)
    {
        $this->loader = $loader;
    }

    /**
     * 获取加载器
     *
     * @return ClassLoader
     */
    public function getLoader()
    {
        return $this->loader;
    }

    /**
     * 设置搜索目录
     *
     * @param array $namespaces
     */
    protected function setScanNamespace(array $namespaces)
    {
        foreach ($namespaces as $namespace) {
            foreach ($this->findDirectoryWithNamespace($namespace) as $dir) {
                if( $dir ){
                    $this->setScanDirectory($dir, $namespace);
                }
            }
        }
    }

    /**
     * 根据命名空间，搜索出对应目录
     *
     * @param string $namespace
     * @return \Generator|string
     */
    private function findDirectoryWithNamespace(string $namespace)
    {
        $loader  = $this->loader;
        $prePsr4 = $loader->getPrefixesPsr4();

        $arrNamespace = explode('\\', $namespace);
        $lastName     = '';
        foreach ($arrNamespace as $key => $name) {
            unset($arrNamespace[$key]);
            if (!$name) {
                break;
            }
            $name = $lastName.$name."\\";
            if (isset($prePsr4[$name])) {
                foreach ($prePsr4[$name] as $dir) {
                    $path = $dir.DIRECTORY_SEPARATOR.implode(DIRECTORY_SEPARATOR, $arrNamespace);
                    yield realpath($path);
                }
                break;
            }
            $lastName = $name;
        }
    }
}