<?php
/**
 * Plugin Name: Credius
 * Plugin URI: https://www.credius.ro/
 * Description: Magento 2.x personal loans integration via Credius.
 * Version: 1.0.0
 * Author: Alexandru Neamtu
 * Author URI: http://github.com/alexneamtu
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Credius_PaymentGateway',
    __DIR__
);
