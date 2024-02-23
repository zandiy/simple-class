<?php

namespace misterxin;

class Zip
{
    /**
     * 根据文件列表压缩文件
     * @param string $path
     * @param string $pathName
     * @return string
     */
    public static function rxs($list, $pathName = '')
    {
        $file = new File;
        $str = "# mister.xin应用文件\n";
        foreach ($list as $v) {
            $rv = get_path() .  $v;
            if (is_file($rv)) {
                $content = str_replace(['@', "\r\n", "\n"], ['_@_', '_@rn@_', '_@n@_'], file_get_contents($rv));
                $str .= '[file ' . $v . ']' . $content . "\n";
            } else {
                $str .= '[dir ' . $v . "]\n";
                $str .= self::rx($rv, true, false);
            }
        }
        $str = trim($str, "\n");
        $file->createFile($pathName, $str);
        return $str;
    }
    /**
     * 根据目录压缩文件
     * @param string $path
     * @param string $completePath
     * @param string $pathName
     * @return string
     */
    public static function rx($path, $completePath = false, $pathName = '')
    {
        $file = new File;
        $head = "# mister.xin应用文件\n";
        if ($pathName !== false) {
            $confPath = $path . '/conf.php';
            if (is_file($confPath)) {
                $conf = include $confPath;
                if (is_array($conf)) {
                    if (isset($conf['id'])) $head .= "# Id: {$conf['id']}\n";
                    if (isset($conf['type'])) $head .= "# Type: {$conf['type']}\n";
                    if (isset($conf['name'])) $head .= "# Name: {$conf['name']}\n";
                    if (isset($conf['intro'])) $head .= "# Intro: {$conf['intro']}\n";
                    if (isset($conf['price'])) $head .= "# Price: {$conf['price']}\n";
                    if (isset($conf['home'])) $head .= "# Home: {$conf['home']}\n";
                    if (isset($conf['author'])) $head .= "# Author: {$conf['author']}\n";
                    if (isset($conf['contact'])) $head .= "# Contact: {$conf['contact']}\n";
                    if (isset($conf['version'])) $head .= "# Version: {$conf['version']}\n";
                    if (isset($conf['limit'])) $head .= "# Limit: {$conf['limit']}\n";
                    $head .= "\n";
                }
            }
        }
        $nPath = $path . '/';
        $run = function ($path) use ($nPath, $completePath, &$run) {
            $str = '';
            $list = glob($path . '/*', GLOB_NOSORT);
            foreach ($list as $v) {
                $vPath = substr($v, strlen($completePath ? get_path() : $nPath));
                if (is_file($v)) {
                    $content = str_replace(['@', "\r\n", "\r", "\n"], ['_@_', '_@rn@_', '_@r@_', '_@n@_'], file_get_contents($v));
                    $str .= '[file ' . $vPath . ']' . $content . "\n";
                } else {
                    $info = pathinfo($v);
                    if ($info['basename'] !== 'compile') {
                        $str .= '[dir ' . $vPath . "]\n";
                        $str .= $run($v);
                    }
                }
            }
            return $str;
        };
        $str = $run($path);
        if ($pathName !== false) $str = $head . 'RX.' . base64_encode(trim($str, "\n"));
        if ($pathName) {
            $file->createFile($pathName, $str);
        }
        return $str;
    }
    /**
     * 解压文件
     * @param string $file
     * @param string $path
     * @return string
     */
    public static function unrx($file, $path = '')
    {
        $path = $path?:get_path();
        $util = new File;
        $content = strlen($file) < 200 && is_file($file) ? file_get_contents($file) : $file;
        $content = explode("\n", $content);
        $arr = [];
        foreach ($content as $v) {
            $v = preg_replace('/^#.*$/', '', trim($v));
            if ($v) $arr[] = $v;
        }
        if (count($arr) === 1 && substr($arr[0], 0, 3) === 'RX.') $arr = explode("\n", base64_decode(substr($arr[0], 3)));
        foreach ($arr as $v) {
            preg_match('/^\[(dir|file)\s(.*?)\](.+?)$/', $v, $m);
            if ($m) {
                $f = str_replace('\\', '/', $m[2]);
                if (substr($f, 0, 1) === '/' || stristr($f, '..') !== false) continue;
                if ($m[1] == 'dir') {
                    $util->createDir($path . $f);
                } elseif ($m[1] == 'file') {
                    $value = str_replace(['_@rn@_', '_@r@_', '_@n@_', '_@_'], ["\r\n", "\r", "\n", '@'], $m[3]);
                    $util->createFile($path . $f, $value);
                }
            }
        }
    }
}
