<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/7
 * Time: 11:58
 */

namespace Apps\Console;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utopia\Console\Console;

class TestConsole extends Console
{
    /**
     * 命令配置
     */
    public function config()
    {
        $this->setName('test:demo')
            ->setDescription('实例自定义命令');
    }

    /**
     * 执行入口
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('OK');
    }
}