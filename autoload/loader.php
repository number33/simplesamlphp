<?php
// The purpose of this file is to setup the paths to the libraries that need to be autoloaded 
// and to switch from composer dependency lookups to PHP include path lookups.  The decisions made
// in this script are modeled after the composer auto-generated autoloader files, where the goal 
// was to keep the namespaces and class name mappings the same as defined in composer yet point to
// the php include paths.

require_once(__DIR__ . '/autoload.php');
// Extract the pear and php directories.
$pearDir = '';
$phpDir = '';
$baseDir = dirname(__DIR__);

$paths = explode(':', get_include_path());
foreach ($paths as $path) {
    switch ($path) {
        case '/usr/share/pear':
            $pearDir = $path;
            break;
        case '/usr/share/php':
            $phpDir = $path;
            break;
        default:
            break;
    }
}

// Check if include paths are set.
$message = '';
if (empty($pearDir)) {
	$message = 'Unable to find PEAR include path. Check if you PHP include_path is set.';
} elseif (empty($phpDir)) {
	$message = 'Unable to find PHP include path. Check if you PHP include_path is set.';
}

if (!empty($message)) {
	if (ini_get('display_errors')) {
		echo($message);
	}
    error_log($message);
    die();
}

$loader = new custom_class_loader();

// This code does the setup of namespaces to directory path mapping so classes can be autoloaded.
// Ex. A call to SimpleSAML_Error_Assertion gets translated into a require for <base library path>/Error/Assertion.php
$map = require __DIR__ . '/autoload_namespaces.php';
foreach ($map as $namespace => $path) {
    $loader->set($namespace, $path);
}

// This code sets up the mapping of class names to class files names for autoloading.
// Ex. A call to Auth_OpenID_AX will require <base library path>/OpenID/AX.php
$classMap = require __DIR__ . '/autoload_classmap.php';
if ($classMap) {
    $loader->addClassMap($classMap);
}

// Register the autoloader class method so that when a class is referenced, the autoloader method will get called to check for the required class file.
$loader->register(true);

// This code simply includes require libraries. This again is modeled after composer's autoloader scripts.
$xmlSecLibsDir = $phpDir . '/xmlseclibs';
$requiredFiles = array(
    $xmlSecLibsDir . '/xmlseclibs.php',
    $baseDir . '/lib/_autoload_modules.php',
);

foreach ($requiredFiles as $file) {
    require $file;
}
