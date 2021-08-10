<?php

class Utils
{
    public static function addFields()
    {
        $orderPending = self::createOrderStatus('Pending (Bleumi)', '#007FFF');
        $orderAwaitingConfirm = self::createOrderStatus('Awaiting Payment Confirmation (Bleumi)', '#34209e');
        $orderOverPaid = self::createOrderStatus('Over Paid (Bleumi)', '#E74C3C');
        $orderPartiallyPaid = self::createOrderStatus('Partially Paid (Bleumi)', '#D0CA64');

        if (
            Configuration::updateValue('BLEUMI_API_KEY', null)
                && Configuration::updateValue('BLEUMI_OS_PENDING', $orderPending)
                && Configuration::updateValue('BLEUMI_OS_PARTIALLY_PAID', $orderPartiallyPaid)
                && Configuration::updateValue('BLEUMI_OS_OVER_PAID', $orderOverPaid)
                && Configuration::updateValue('BLEUMI_OS_AWAITING_CONFIRM', $orderAwaitingConfirm)
        ) {
            return true;
        }

        return false;
    }

    public static function createOrderStatus($name, $color)
    {
        $searchState = self::getStateForName($name);
        $orderStateID = $searchState['id_order_state'];
        if (!empty($orderStateID)) {
            PrestaShopLogger::addLog('[BLEUMI] order state ' .  $name . ' already exists ' . $orderStateID);
            return $orderStateID;
        }

        $orderState = new OrderState();
        $orderState->name = array_fill(0, 10, $name);
        $orderState->send_email = 0;
        $orderState->invoice = 0;
        $orderState->color = $color;
        $orderState->unremovable = false;
        $orderState->logable = 0;
        
        if ($orderState->add()) {
            $source = _PS_MODULE_DIR_ . 'Bleumi/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $orderState->id . '.gif';

            copy($source, $destination);
        }
        
        return $orderState->id;
    }

    public static function getStateForName($name)
    {
        $db = Db::getInstance();
        $result = null;
        
        $SQL = "SELECT *
                FROM `" . _DB_PREFIX_ . "order_state_lang`
                WHERE `id_lang` = 1 AND `name` = '" . $name . "'";
        
        $result = $db->ExecuteS($SQL);
        
        if (count($result) > 0) {
            return $result[0];
        } else {
            return false;
        }
    }

    public static function update_order_status($order_id, $response)
    {
        $order = new Order($order_id);
        $order_current_status = $order->current_state;

	PrestaShopLogger::addLog('[BLEUMI] update_order_status: ' . $order_id . ', order_status: ' . $order_current_status . ', response: ' . json_encode($response));

        if ($order_current_status !== Configuration::get('PS_OS_PAYMENT') && !empty($response['record'])) {
            $amt_due = floatval($response['record']['amt_due']);
            $amt_recv_pending = floatval($response['record']['amt_recv_online_pending']);

            PrestaShopLogger::addLog('[BLEUMI] amt_due: ' . $amt_due . " for orderId #" . $order_id);
            PrestaShopLogger::addLog('[BLEUMI] amt_recv_pending: ' . $amt_recv_pending . " for orderId #" . $order_id);
            PrestaShopLogger::addLog('[BLEUMI] total: ' . $response['record']['total'] . " for orderId #" . $order_id);
            PrestaShopLogger::addLog('[BLEUMI] Awaiting: ' . Configuration::get('BLEUMI_OS_AWAITING_CONFIRM'));

            if ($amt_recv_pending > 0 && $order_current_status !== Configuration::get('BLEUMI_OS_AWAITING_CONFIRM')) {
                $order_current_status = (int)Configuration::get('BLEUMI_OS_AWAITING_CONFIRM');
            } else {
                if ($amt_due > 0) {
                    if ($response['record']['amt_due'] === $response['record']['total']) {
                        PrestaShopLogger::addLog('[BLEUMI] Customer Marked as: ' . "Paid " . '#' . $order_id);
                        return;
                    } else {
                        if ($order_current_status !== Configuration::get('BLEUMI_OS_PARTIALLY_PAID')) {
                            $order_current_status = (int)Configuration::get('BLEUMI_OS_PARTIALLY_PAID');
                        }
                    }
                } elseif ($amt_due < 0 && $order_current_status !== Configuration::get('BLEUMI_OS_OVER_PAID')) {
                    $order_current_status = (int)Configuration::get('BLEUMI_OS_OVER_PAID');
                } else {
                    if ($order_current_status !== Configuration::get('PS_OS_PAYMENT')) {
                        $order_current_status = (int)Configuration::get('PS_OS_PAYMENT');
                    }
                }
            }

            $history = new OrderHistory();
            $history->id_order = $order_id;
            $history->changeIdOrderState($order_current_status, $order_id);
            $history->addWithemail(true, array(
                'order_name' => Tools::getValue('order_id'),
            ));
            $state = $order->getCurrentStateFull($order_current_status);
            
            PrestaShopLogger::addLog('[BLEUMI] Order status Marked as: ' . $state['name'] . ' #' . $order_id);
        }
    }
}
