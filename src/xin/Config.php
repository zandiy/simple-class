<?php
namespace misterxin;

class Config
{
    private static $config = [];
    /**
     *  XIN 加载配置文件
     */
    public static function load( $name , $path='' )
    {
        $path = $path?:get_path('config');
        if (isset(self::$config[$name]))  return;
        $file = $path . $name . '.php';
        if (!is_file($file)) throw new \Exception($file.'配置文件不存在', 3001);
        self::$config[$name] = include($file);
    }
    /**
     * 设置配置
     */
    public static function set($name, $value)
    {
        $names = explode('.', $name);
        $result = [];
        $current = &$result;
        foreach ($names as $val) {
            $current[$val] = [];
            $current = &$current[$val];
        }
        $current = $value;
        $result2 = array();
        foreach (self::$config as $key => $value) {
            if (isset($result[$key])) {
                // 当两个数组都有相同的键时，将对应值进行合并
                $result2[$key] = array_merge($value, $result[$key]);
            } else {
                // 只存在于第一个数组的键则直接复制到结果数组中
                $result2[$key] = $value;
            }
        }
        // 添加第二个数组中不包含的键及其值
        $result2 += $result;

        self::$config = $result2;
        // self::$config = array_merge($result,self::$config);
    }
    /**
     * 获取配置
     */
    public static function get($name)
    {
        $offset    = explode('.', $name);
        $offset[0] = strtolower($offset[0]);
        self::load($offset[0]);
        $config  = self::$config;
        foreach ($offset as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                return null;
            }
        }
        return $config;
    }
}