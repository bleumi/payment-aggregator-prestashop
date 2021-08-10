<?php

class BleumiCancelModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        try {
            $url = urldecode($_SERVER['REQUEST_URI']);
            $url_components = parse_url($url);
            parse_str($url_components['query'], $params);

            PrestaShopLogger::addLog("[BLEUMI] query_string " . $params['order_id']);
            
            $history = new OrderHistory();
            $history->id_order = $params['order_id'];
            $history->changeIdOrderState((int)Configuration::get('PS_OS_CANCELED'), $params['order_id']);
            $history->addWithemail(true, array(
                'order_name' => Tools::getValue('order_id'),
            ));
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('[BLEUMI] Unable to get Query string ' . $e->getMessage(), 3);
        }
        
        Tools::redirect('index.php?controller=order&step=1');
    }
}
