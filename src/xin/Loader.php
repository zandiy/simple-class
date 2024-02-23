<?php

namespace misterxin;

class Loader
{
	public static function getInstance($className)
	{
		$paramArr = self::getMethodParams($className);
		return (new \ReflectionClass($className))->newInstanceArgs($paramArr);
	}

	public static function make($className, $methodName, $params = [])
	{
		$instance = self::getInstance($className);
		$paramArr = self::getMethodParams($className, $methodName);
		return $instance->{$methodName}(...array_merge($paramArr, $params));
	}

	protected static function getMethodParams($className, $methodsName = '__construct')
	{
		$class = new \ReflectionClass($className);
		$paramArr = [];
		if ($class->hasMethod($methodsName)) {
			$method = $class->getMethod($methodsName);
			$params = $method->getParameters();
			if (count($params) > 0) {
				foreach ($params as $key => $param) {
					// php8启用getClass()
					$type = $param->getType();
					if ($type && !$type->isBuiltin() && $type instanceof \ReflectionNamedType) {
						$paramClassName = $type->getName();
						$args = self::getMethodParams($paramClassName);
						$paramArr[] = (new \ReflectionClass($paramClassName))->newInstanceArgs($args);
					}
				}
			}
		}
		return $paramArr;
	}
}