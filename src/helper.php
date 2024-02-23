<?php

use misterxin\App;
use misterxin\Config;
use misterxin\Loader;
use misterxin\Request;

if(!function_exists('get_path')){
    function get_path(string $name = 'root'){
        return App::getInstance()->getPath($name);
    }
}


/**
 * 获取配置
 */
if (!function_exists('config')){
    function config($name, $value = ''){
        if ($value) return Config::set($name, $value);
        return Config::get($name);
    }
}
/**
 * 获取当前模板目录
 */
if (!function_exists('tpl_path')){
    function tpl_path(){
        return '/template/'.config('app.tpl').'/';
    }
}

if (!function_exists('json')){
    function json($data){
        header('Content-Type:application/json; charset=utf-8');
		//echo json_encode($data, JSON_UNESCAPED_UNICODE);
		exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('error')){
    function error( $msg = 'error',$code = 1 ){
        header('Content-Type:application/json; charset=utf-8');
		$data = [
            'code'=>$code,
            'msg' =>$msg
        ];
		exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('success')){
    function success( $datas = [],$msg = 'success',$code = 0 ){
        header('Content-Type:application/json; charset=utf-8');
		$data = [
            'code'=>$code,
            'msg' =>$msg,
            'data'=>$datas
        ];
		exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }
}

if (!function_exists('url')){
    function url( $path ){
        return '/?s='.$path;
    }
}

if (!function_exists('is_post')){
    function is_post(){
        return (Request::getInstance())->isPost();
    }
}

if (!function_exists('action')){
    function action($controller , $func ,$params = []){
        if(class_exists($controller)){
            return Loader::make( $controller , $func ,$params);
        }
        $controller = trim( $controller , '\\' );
        if( strpos( $controller , '\\' ) !== false ){
            $ctrlarr = explode('\\',$controller);
            $app = $ctrlarr[0];
            $controller = ucfirst($ctrlarr[1]);
        }else{
            $app = 'admin';
            $controller = ucfirst($controller);
        }
        $ctrl = '\app\\'.$app.'\\'.$controller;
        return Loader::make( $ctrl , $func ,$params);
    }
}

if (!function_exists('save')){
    function save($path , array $data){
        return file_put_contents($path, "<?php\nreturn ".var_export($data, true).";");
    }
}