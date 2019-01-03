<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Boxtal <api@boxtal.com>
 * @copyright 2007-2019 PrestaShop SA / 2018-2019 Boxtal
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

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
 * @param string $className the fully-qualified name of the class to load
 */
//phpcs:ignore
function boxtalConnectAutoload($className)
{
    // If the specified $className does not include our namespace, duck out.
    if (false === strpos($className, 'Boxtal\BoxtalConnectPrestashop')
        && false === strpos($className, 'Boxtal\BoxtalPhp')) {
        return;
    }

    // Split the class name into an array to read the namespace and class.
    $fileParts = explode('\\', $className);

    if (count($fileParts) < 3) {
        return;
    }

    $path = '';
    for ($i = count($fileParts) - 1; $i > 1; --$i) {
        if (count($fileParts) - 1 === $i) {
            $path .= $fileParts[$i] . '.php';
        } else {
            $path = Tools::strtolower($fileParts[$i]) . '/' . $path;
        }
    }

    if ('BoxtalPhp' === $fileParts[1]) {
        $filePath = dirname(__FILE__) . '/lib/' . $path;
    } elseif ('BoxtalConnectPrestashop' === $fileParts[1]) {
        $filePath = dirname(__FILE__) . '/' . $path;
    }

    // If the file exists in the specified path, then include it.
    if (file_exists($filePath)) {
        include_once $filePath;
    } else {
        var_dump("The file attempting to be loaded at $filePath does not exist.");
    }
}
