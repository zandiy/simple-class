<?php

namespace misterxin;

class App
{
    protected $path=[];
    private static $instance;

    public function __construct()
    {
        $this->init();
    }
    public function init()
    {
        require_once(dirname(__FILE__,2).DIRECTORY_SEPARATOR.'helper.php');
    }

    public static function getInstance()
    {
        if(!self::$instance || !(self::$instance instanceof App)){
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function run()
    {
        Router::alloc();
    }

    public function getPath(string $name = 'root'):string
    {
        if(!isset($this->path[$name])){
            $root = dirname(__DIR__,4).DIRECTORY_SEPARATOR;
            $arrpath = [
                'root'  => $root,
                'xin'   => __DIR__.DIRECTORY_SEPARATOR
            ];
            $path = $root.$name.DIRECTORY_SEPARATOR;
            if(!is_dir($path)) throw new \Exception($path.'目录获取异常');
            $this->path[$name] = $arrpath[$name] ?? $path;
        }
        return $this->path[$name];
    }
}
