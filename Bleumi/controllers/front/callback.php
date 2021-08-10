<?php

if (defined('_PS_MODULE_DIR_')) {
    require_once(_PS_MODULE_DIR_ . '/Bleumi/classes/init.php');
}

class BleumiCallbackModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {
        $body = Tools::file_get_contents("php://input");
        $request = json_decode($body, true);
        $order_id = $request['id'];

	PrestaShopLogger::addLog("[BLEUMI] Callback Order Id: " . $order_id);
        
        try {
            if (!empty($order_id)) {
                $response = APIHandler::getCurlResponse($order_id, "GET");
                PrestaShopLogger::addLog("[BLEUMI] Callback response : " . json_encode($response));
                
                Utils::update_order_status(intval(explode('__', $request['id'])[0]), $response);
            }
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('[BLEUMI] Bleumi payment validation failed ' . $e->getMessage(), 3);
        }
    }
}
