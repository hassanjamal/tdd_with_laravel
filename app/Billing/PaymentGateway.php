<?php
/**
 * Created by PhpStorm.
 * User: hassanjamal
 * Date: 11/04/19
 * Time: 1:44 AM
 */

namespace App\Billing;

interface PaymentGateway
{
    public function charge($amount, $token);
}