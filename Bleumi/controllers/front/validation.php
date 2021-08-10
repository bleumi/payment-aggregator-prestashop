<?php

class BleumiValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            $logMessage = sprintf(
                'Bleumi: order validation failed. id_customer: %s, id_address_delivery: %s, id_address_invoice: %s',
                $cart->id_customer,
                $cart->id_address_delivery,
                $cart->id_address_invoice
            );
            
            PrestaShopLogger::addLog('[BLEUMI] ' . $logMessage, 1, null, 'Cart', $cart->id, true);
            Tools::redirect('index.php?controller=order&step=1');
        }

        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'Bleumi') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->module->l('This payment method is not available.', 'validation'));
        }
        
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $logMessage = sprintf('Bleumi: Order validation failed. Customer not found (%s)', $cart->id_customer);
            PrestaShopLogger::addLog('[BLEUMI] ' . $logMessage, 1, null, 'Cart', $cart->id, true);
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $mailVars = array();

        $this->module->validateOrder($cart->id, Configuration::get('BLEUMI_OS_PENDING'), 0, $this->module->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);
        if ((float) _PS_VERSION_ < 1.7) {
            $order = new Order(Order::getOrderByCartId($cart->id));
        } else {
            $order = new Order(Order::getIdByCartId($cart->id));
        }

        $link = $this->context->link->getModuleLink($this->module->name, 'redirect', array('order' => $order->reference));
        Tools::redirect($link);
    }
}
