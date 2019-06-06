<?php

declare (strict_types=1);

class Container
{
    /**
     * 容器绑定标识
     */
    private $bind = [];

    /**
     * 容器中的对象实例
     */
    protected $instances = [];

    /**
     * 容器对象实例
     */
    private static $instance = null;

    /**
     * 获取当前容器的实例（单例）
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static;
        }

        if (static::$instance instanceof Closure) {
            return (static::$instance)();
        }

        return static::$instance;
    }

    /**
     * instance
     *
     * @param $abstract
     * @param $instance
     *
     * @return $this|Container
     * @author King
     */
    public function instance($abstract, $instance)
    {
        if (isset($this->bind[$abstract])) {
            $bind = $this->bind[$abstract];

            if (is_string($bind)) {
                return $this->instance($bind, $instance);
            }
        }

        $this->instances[$abstract] = $instance;

        return $this;
    }

    /**
     * 绑定一个类、闭包、实例、接口实现到容器
     *
     * @param string|array $abstract 类标识、接口
     * @param null         $concrete 要绑定的类、闭包或者实例
     *
     * @return $this
     * @author King
     */
    public function bind($abstract, $concrete = null)
    {
        if (is_array($abstract)) {
            $this->bind = array_merge($this->bind, $abstract);
        } elseif ($concrete instanceof Closure) {
            $this->bind[$abstract] = $concrete;
        } elseif (is_object($concrete)) {
            $this->instance($abstract, $concrete);
        } else {
            $this->bind[$abstract] = $concrete;
        }

        return $this;
    }

    /**
     * 创建类的实例 已经存在则直接获取
     *
     * @param string $abstract 类名或者标识
     * @param array  $vars 变量
     *
     * @return mixed|object
     * @author King
     */
    public function make(string $abstract, array $vars = [])
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        if (isset($this->bind[$abstract])) {
            $concrete = $this->bind[$abstract];

            if ($concrete instanceof Closure) {
                $object = $this->invokeFunction($concrete, $vars);
            } else {
                return $this->make($concrete, $vars);
            }
        } else {
            $object = $this->invokeClass($abstract, $vars);
        }

        return $object;
    }

    /**
     * 获取容器中的对象实例
     *
     * @param string $abstract 类名或者标识
     *
     * @return mixed|object
     * @author King
     */
    public function get($abstract)
    {
        return $this->make($abstract);
    }

    /**
     * 执行函数或者闭包方法 支持参数调用
     *
     * @param mixed $function 函数或者闭包
     * @param array $vars 参数
     *
     * @return mixed
     * @throws Exception
     * @author King
     */
    public function invokeFunction($function, $vars = [])
    {
        try {
            $reflect = new ReflectionFunction($function);

            $args = $this->bindParams($reflect, $vars);

            return call_user_func_array($function, $args);
        } catch (ReflectionException $reflectionException) {
            throw new Exception('function not exists: ' . $function . '()');
        }
    }

    /**
     * 调用反射执行类的实例化 支持依赖注入
     *
     * @param string $class 类名
     * @param array  $vars 参数
     *
     * @return object
     * @throws Exception
     * @author King
     */
    public function invokeClass(string $class, $vars = [])
    {
        try {
            $reflect = new ReflectionClass($class);
            if ($reflect->hasMethod('__make')) {

            }

            $constructor = $reflect->getConstructor();

            $args = $constructor ? $this->bindParams($reflect, $vars) : [];

            return $reflect->newInstanceArgs($args);
        } catch (ReflectionException $reflectionException) {
            throw new Exception('class not exists: ' . $class);
        }
    }

    /**
     * 绑定参数
     *
     * @param \ReflectionMethod|\ReflectionFunction $reflect 反射类
     * @param array                                 $vars 参数
     *
     * @return array
     * @author King
     */
    public function bindParams($reflect, $vars = [])
    {
        if ($reflect->getNumberOfParameters() == 0) {
            return [];
        }

        reset($vars);
        $type   = key($vars) === 0 ? 1 : 0;   // 1: 索引数组；2：关联数组
        $params = $reflect->getParameters();

        $args = [];
        foreach ($params as $param) {
            $name  = $param->getName();
            $class = $param->getClass();

            if ($class) {
                $args[] = $this->getObjectParam($class->getName(), $vars);
            } elseif ($type == 1 && !empty($vars)) {
                $args[] = array_shift($vars);
            } elseif ($type == 0 && isset($vars[$name])) {
                $args[] = $vars[$name];
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                throw new InvalidArgumentException('method param miss:' . $name);
            }
        }

        return $args;
    }

    /**
     * 获取对象类型的参数值
     *
     * @param string $className 类名
     * @param array  $vars 参数
     *
     * @return mixed|object
     * @author King
     */
    public function getObjectParam(string $className, array &$vars)
    {
        $array = $vars;
        $value = array_shift($array);

        if ($value instanceof $className) {
            $result = $value;
            array_shift($vars);
        } else {
            $result = $this->make($className);
        }

        return $result;
    }
}


