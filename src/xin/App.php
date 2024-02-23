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
            $root = dirname(__DIR__,5).DIRECTORY_SEPARATOR;
            $arrpath = [
                'root'  => $root,
                'xin'   => __DIR__.DIRECTORY_SEPARATOR
            ];
            $path = $root.$name.DIRECTORY_SEPARATOR;
            $this->path[$name] = $arrpath[$name] ?? $path;
            if(!is_dir($this->path[$name])) throw new \Exception($path.'目录获取异常');
        }
        return $this->path[$name];
    }
}
