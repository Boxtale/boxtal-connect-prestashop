<?php
/**
 * Mock payment helper
 */

/**
 * Class MockPayment to use PaymentModule
 */
class MockPayment extends PaymentModule
{
    public $active = 1;
    public $name = 'mock_payment';
    public $displayName = 'mock_payment';
}
