<?php

if (defined('_PS_MODULE_DIR_')) {
    require_once(_PS_MODULE_DIR_ . '/Bleumi/classes/init.php');
}

class BleumiRedirectModuleFrontController extends ModuleFrontController
{

    public function initContent()
    {
        parent::initContent();

        $orderReference = Tools::getValue('order');
        $order = Order::getByReference($orderReference)->getFirst();

        // Get Customer Details
        $customer = $order->getCustomer();
        $currency = Currency::getCurrencyInstance((int)$order->id_currency);

        $order_id = $order->id;
        $total = $order->total_paid;

        $link = new Link();
        if ((float) _PS_VERSION_ < 1.7) {
            $successUrl =  $link->getPageLink('order-detail', null, null, array(
                'id_order'     => $order->id,
                'key'         => $customer->secure_key
            ));
        } else {
            $successUrl = $link->getPageLink('order-confirmation', null, null, array(
                'id_cart'     => $order->id_cart,
                'id_module'   => $this->module->id,
                'key'         => $customer->secure_key
            ));
        }
        
        $currency = Context::getContext()->currency;
        $uniq_order_id = $order_id . '__' . time();
        $cancelUrl = $this->context->link->getModuleLink('Bleumi', 'cancel', array('order_id' => $order_id));;
        $notifyUrl = $this->context->link->getModuleLink('Bleumi', 'callback');
        
        $requestParams = array(
            "id" => $uniq_order_id,
            "currency" => $currency->iso_code,
            "invoice_date" => intval(date("Ymd")),
            "allow_partial_payments" => true,
            "metadata" => array(
                "no_invoice" => true
            ),
            "success_url" => $successUrl,
            "cancel_url" => $cancelUrl,
            "notify_url" => $notifyUrl,
            "record" => array(
                "client_info" => array(
                    "type" => "individual",
                    "name" => $customer->firstname . ' ' . $customer->lastname,
                    "email" => $customer->email
                ),
                "line_item" => array(
                    array(
                        "name" => "Order #" . $order_id,
                        "description" => $order->reference,
                        "quantity" => 1,
                        "rate" => (string)$total
                    )
                )
            )
        );
        
        $result =  APIHandler::getCurlResponse($requestParams, "POST");
        if ($result) {
            if (!$result['payment_url']) {
                Tools::redirect('index.php?controller=order&step=3');
            }
            Tools::redirect($result["payment_url"]);
        } else {
            Tools::redirect('index.php?controller=order&step=3');
        }
    }
}
