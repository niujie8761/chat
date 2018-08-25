<?php
//namespace app\admin\command;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\Config;
use app\admin\model\Blog;


class Test extends Command
{
    protected function configure()
    {
        $this->setName('test')->setDescription('Here is the remark ');
    }

    protected function execute(Input $input, Output $output)
    {
        Config::load(APP_PATH.'index/config.php');
        echo APP_PATH.'index/config.php';
        $config =   Config::get('ceshi');
        Blog::test();
        $output->writeln("nihao");
    }
}
