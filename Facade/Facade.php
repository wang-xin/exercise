<?php

declare (strict_types=1);

require_once '../Container/Container.php';

/**
 * Facade管理类
 */
class Facade
{
    /**
     * 创建Facade实例
     *
     * @param string $class 类名或标识
     * @param array  $args 变量
     *
     * @return mixed
     * @author King
     */
    protected static function createFacade(string $class = '', array $args = [])
    {
        $class = $class ?: static::class;

        $facadeClass = static::getFacadeClass();
        if ($facadeClass) {
            $class = $facadeClass;
        }

        return Container::getInstance()->make($class, $args);
    }

    /**
     * 获取当前Facade对应类名
     * @author King
     */
    protected static function getFacadeClass()
    {

    }

    /**
     * 调用实际类的方法
     *
     * @param $method
     * @param $params
     *
     * @return mixed
     * @author King
     */
    public static function __callStatic($method, $params)
    {
        return call_user_func_array([static::createFacade(), $method], $params);
    }
}
