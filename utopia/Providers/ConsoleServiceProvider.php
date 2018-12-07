<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/6
 * Time: 20:43
 */

namespace Utopia\Providers;


use Symfony\Component\Console\Application;

/**
 * 命令行引导
 * @package Utopia\Providers
 */
class ConsoleServiceProvider extends ServiceProvider
{
    protected $paths = [];

    public function register()
    {
        $this->paths = [
            \Utopia\Application::getRootPath('/utopia/Console')=>"Utopia\\Console\\",
            \Utopia\Application::getRootPath('/apps/Console')=>"Apps\\Console\\",
        ];
    }

    /**
     * 所有 register() 都执行完之后执行 boot()
     * @return mixed
     */
    public function boot()
    {
        $app = new Application('UTOPIA', '0.0.1');
        $app->setAutoExit(false);

        foreach ($this->paths as $directory=>$nameSpace){
            foreach ($this->getFiles($directory,$nameSpace) as $class){
                $class = new $class();
                $app->add($class);
            }
        }

        $this->bindService('console', $app);
    }


    /**
     * 遍历命名空间
     *
     * @param $directory
     * @param $nameSpace
     * @return \Generator
     */
    private function getFiles($directory,$nameSpace)
    {
        if ($dh = opendir($directory)) {
            while (($file = readdir($dh)) !== false) {
                if (in_array($file, ['.', '..','Console.php'])) {
                    continue;
                } elseif (is_dir($directory.DIRECTORY_SEPARATOR.$file)) {
                    foreach ($this->getFiles(
                        $directory.DIRECTORY_SEPARATOR.$file,
                        $nameSpace.$file."\\"
                    ) as $arrValue) {
                        yield $arrValue;
                    }
                } elseif (substr($file, -4) == '.php') {
                    yield $nameSpace.pathinfo($file, PATHINFO_FILENAME);
                }
            }
            closedir($dh);
        }
    }
}