<?php
/**
 * Created by PhpStorm.
 * User: 明月有色
 * Date: 2018/12/7
 * Time: 10:56
 */

namespace Utopia\Console\Http;


use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Utopia\Application;
use Utopia\Console\Console;
use Utopia\SocketServer\HttpServer;
use Utopia\Utopia;

class StartConsole extends Console
{
    /**
     * 命令配置
     */
    public function config()
    {
        $this->setName('http:start')
            ->setDescription('http服务器启动')
            ->addArgument('debug', InputArgument::OPTIONAL, '启动调试', false);
    }

    /**
     * 执行入口
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    public function handle(InputInterface $input, OutputInterface $output)
    {
        /** @var Utopia $utopia */
        $utopia = Application::get('utopia');

        $utopia->addSocket(new HttpServer(8080));

        Application::$runUtopia = true;
    }
}