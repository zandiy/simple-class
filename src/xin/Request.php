<?php
namespace misterxin;

class Request
{
    private static $instance;
    private $server;
    private $path;
    private $query;
    private $s;

    private function __construct()
    {
        if(config('app.rewrite')){
            $this->pathinfo();
        }else{
            $this->dynamics();
        }
    }
    /**
     * 动态连接
     */
    protected function dynamics()
    {
        $this->server = $_SERVER;
        $s = $_GET['s'] ?? 'index';
        $s = !empty($s)?$s:'index';
        $s = trim(trim($s,' '),'/');
        $this->s = $s;
    }
    public function getSparams(){
        return $this->s;
    }
    protected function pathinfo()
    {
        $this->server = $_SERVER;
        $url = parse_url($_SERVER['REQUEST_URI']);
        $this->path   = $url['path'];
        $this->query  = $url['query'];
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function server()
    {
        return $this->server;
    }

    public function urlParsePath()
    {
        $path = trim($this->path,'/');
        $arrpath = [];
        if($path){
            $arrpath = explode('/',$path);
        }
        return $arrpath;
    }
    public function getPath()
    {
        return $this->path;
    }
    public function getQuery()
    {
        return $this->query;
    }

    public function isPost()
    {
        return $this->server['REQUEST_METHOD'] == 'POST';
    }
    public function isGet()
    {
        return $this->server['REQUEST_METHOD'] == 'GET';
    }

}