<?php

namespace misterxin;

class Router
{
    public static function alloc()
    {
        $path = (Request::getInstance())->parsePath();
        $patharr = explode('/', trim($path,'/'));
        $patharr = array_map('strtolower',$patharr);

        $app    = $patharr[0] ?? 'index';
        $ctrl   = $patharr[1] ?? 'index';
        $ctrl   = ucfirst($ctrl);
        $action = $patharr[2] ?? 'index';
        $params = array_map(function($arg) {
			return strip_tags(htmlspecialchars(stripslashes($arg)));
		}, array_slice($patharr, 3));

        $controller = '\app\\' . $app . '\\' . $ctrl;
        if (!class_exists($controller)) {
			throw new \Exception($controller.'控制器不存在');
		}
        Loader::make($controller, $action, $params);
    }
}
