<?php

/**
 * The purpose of this class is to autoload SimpleSAMLphp classes
 * and all of it's depdencies.  It is refactored from Composer's autoloader class by
 * Nils Adermann <naderman@naderman.de> Jordi Boggiano <j.boggiano@seld.be>
 */
class custom_class_loader {
    /* An array of classes to library files. */
    private $classMap = array();
    // PSR-0
    private $prefixesPsr0 = array();
    private $fallbackDirsPsr0 = array();

    /**
     * Registers this instance as an autoloader.
     *
     * @param bool $prepend Whether to prepend the autoloader or not
     */
    public function register($prepend = false) {
        spl_autoload_register(array($this, 'loadClass'), true, $prepend);
    }

    /**
     * Unregisters this instance as an autoloader.
     */
    public function unregister() {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * This function was refactored from Composer's ClassLoader.php script.
     * @param array $classMap Class to filename map
     */
    public function addClassMap(array $classMap)
    {
        if ($this->classMap) {
            $this->classMap = array_merge($this->classMap, $classMap);
        } else {
            $this->classMap = $classMap;
        }
    }

    /**
     * Registers a set of PSR-0 directories for a given prefix,
     * replacing any others previously set for this prefix.
     * This function was refactored from Composer's ClassLoader.php script.
     *
     * @param string       $prefix The prefix
     * @param array|string $paths  The PSR-0 base directories
     */
    public function set($prefix, $paths)
    {
        if (!$prefix) {
            $this->fallbackDirsPsr0 = (array) $paths;
        } else {
            $this->prefixesPsr0[$prefix[0]][$prefix] = (array) $paths;
        }
    }


    /**
     * Loads the given class or interface. This function is refactored from Composer's ClassLoader.php script.
     *
     * @param  string    $class The name of the class
     * @return bool|null True if loaded, null otherwise
     */
    public function loadClass($class) {
        if ($file = $this->findFile($class)) {
            includeFile($file);

            return true;
        }
    }

    /**
     * Finds the path to the file where the class is defined. This function is refactored from Composer's ClassLoader.php script.
     *
     * @param string $class The name of the class
     * @return string|false The path if found, false otherwise
     */
    public function findFile($class)
    {
        // work around for PHP 5.3.0 - 5.3.2 https://bugs.php.net/50731
        if ('\\' == $class[0]) {
            $class = substr($class, 1);
        }

        // class map lookup
        if (isset($this->classMap[$class])) {
            return $this->classMap[$class];
        }

        $file = $this->findFileWithExtension($class, '.php');

        if ($file === null) {
            // Remember that this class does not exist.
            return $this->classMap[$class] = false;
        }

        return $file;
    }

    /**
     * This function is refactored from Composer's ClassLoader.php script.
     */
    private function findFileWithExtension($class, $ext) {
        // PSR-4 lookup
        $logicalPathPsr4 = strtr($class, '\\', DIRECTORY_SEPARATOR) . $ext;

        $first = $class[0];

        // PSR-0 lookup
        if (false !== $pos = strrpos($class, '\\')) {
            // namespaced class name
            $logicalPathPsr0 = substr($logicalPathPsr4, 0, $pos + 1)
                . strtr(substr($logicalPathPsr4, $pos + 1), '_', DIRECTORY_SEPARATOR);
        } else {
            // PEAR-like class name
            $logicalPathPsr0 = strtr($class, '_', DIRECTORY_SEPARATOR) . $ext;
        }

        if (isset($this->prefixesPsr0[$first])) {
            foreach ($this->prefixesPsr0[$first] as $prefix => $dirs) {
                if (0 === strpos($class, $prefix)) {
                    foreach ($dirs as $dir) {
                        if (file_exists($file = $dir . DIRECTORY_SEPARATOR . $logicalPathPsr0)) {
                            return $file;
                        }
                    }
                }
            }
        }
    }
}

/**
 * Scope isolated include. This function is refactored from Composer's ClassLoader.php script.
 *
 * Prevents access to $this/self from included files.
 */
function includeFile($file) {
    include $file;
}
