<?php
namespace Itxiao6\Upload;

class Autoloader
{
    /**
     * The project's base directory
     * @var string
     */
    static protected $base;

    /**
     * Register autoloader
     */
    static public function register()
    {
        self::$base = dirname(__FILE__) . '/../';
        spl_autoload_register(array(new self, 'autoload'));
    }

    /**
     * Autoload classname
     * @param  string $className The class to load
     */
    static public function autoload($className)
    {
        $className = ltrim($className, '\\');
        $fileName  = '';
        $namespace = '';
        if ($lastNsPos = strripos($className, '\\')) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

        require self::$base . $fileName;
    }
}
