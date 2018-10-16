<?php
/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin.
 */

spl_autoload_register('boxtalConnectAutoload');

/**
 * Dynamically loads the class attempting to be instantiated elsewhere in the
 * plugin by looking at the $class_name parameter being passed as an argument.
 *
 * The argument should be in the form: Boxtal\BoxtalConnectPrestashop\Namespace. The
 * function will then break the fully-qualified class name into its pieces and
 * will then build a file to the path based on the namespace.
 *
 * @param string $className The fully-qualified name of the class to load.
 */
//phpcs:ignore
function boxtalConnectAutoload($className)
{

    // If the specified $className does not include our namespace, duck out.
    if (false === strpos($className, 'Boxtal\BoxtalConnectPrestashop') && false === strpos($className, 'Boxtal\BoxtalPhp')) {
        return;
    }

    // Split the class name into an array to read the namespace and class.
    $fileParts = explode('\\', $className);

    if (count($fileParts) < 3) {
        return;
    }

    $path = '';
    for ($i = count($fileParts) - 1; $i > 1; $i--) {
        if (count($fileParts) - 1 === $i) {
            $path .= $fileParts[$i].'.php';
        } else {
            $path = strtolower($fileParts[$i]).'/'.$path;
        }
    }

    if ('BoxtalPhp' === $fileParts[1]) {
        $filePath = __DIR__.'/lib/'.$path;
    } elseif ('BoxtalConnectPrestashop' === $fileParts[1]) {
        $filePath = __DIR__.'/'.$path;
    }

    // If the file exists in the specified path, then include it.
    if (file_exists($filePath)) {
        include_once $filePath;
    } else {
        var_dump("The file attempting to be loaded at $filePath does not exist.");
    }
}
