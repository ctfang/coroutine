<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/7
 * Time: 11:01
 */

namespace Utopia\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class Console extends Command
{
    protected function configure()
    {
        $this->config();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|mixed|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->handle($input, $output);
    }

    /**
     * 命令配置
     */
    abstract public function config();

    /**
     * 执行入口
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    abstract public function handle(InputInterface $input, OutputInterface $output);
}