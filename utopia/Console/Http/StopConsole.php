<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/7
 * Time: 20:55
 */

namespace Utopia\Console\Http;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utopia\Console\Console;

class StopConsole extends Console
{
    /**
     * 命令配置
     */
    public function config()
    {
        $this->setName('http:start')
            ->setDescription('http服务器关闭');
    }

    /**
     * 执行入口
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        // TODO: Implement handle() method.
    }
}