<?php
/**
 * Mock plugin class
 */

class MockPlugin
{
    public $minPhpVersion;

    /**
     * Mock prestashop module l function.
     *
     * @param string $arg string to translate.
     *
     * @return string localized string
     */
    public function l($arg)
    {
        return $arg;
    }
}
