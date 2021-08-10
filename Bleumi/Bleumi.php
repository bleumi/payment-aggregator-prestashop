<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
if (defined('_PS_MODULE_DIR_')) {
    require_once(_PS_MODULE_DIR_ . '/Bleumi/classes/init.php');
}


class Bleumi extends PaymentModule
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'Bleumi';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'Bleumi';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->is_eu_compatible = 1;
        
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('Bleumi Payments for Prestashop');
        $this->description = $this->l('Accept Traditional and Crypto Currency Payments');

        $this->confirmUninstall = $this->l('Are you sure you want to delete your details?');
    }

    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');
            return false;
        }
        
        return parent::install() &&
            Utils::addFields() &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentOptions');
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        if (((bool)Tools::isSubmit('submitBleumiModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitBleumiModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Your API Key.  Create one at ') . '<a href="https://account.bleumi.com/account/?app=payment" target="_blank">https://account.bleumi.com/account/?app=payment</a>',
                        'name' => 'BLEUMI_API_KEY',
                        'label' => $this->l('API Key'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'label' => $this->l('Title'),
                        'name' => 'BLEUMI_TITLE',
                        'desc' => $this->l('This controls the title which the user sees during checkout.'),
                    ),
                    array(
                        'col' => 6,
                        'type' => 'text',
                        'label' => $this->l('Description'),
                        'name' => 'BLEUMI_DESCRIPTION',
                        'desc' => $this->l('This is the message box that will appear on the ') . '<b>checkout page</b>' . $this->l(' when they select Bleumi Payments.'),

                    ),
                ),


                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    protected function getConfigFormValues()
    {
        return array(
            'BLEUMI_API_KEY' => Configuration::get('BLEUMI_API_KEY', null),
            'BLEUMI_TITLE' =>  Configuration::get('BLEUMI_TITLE', 'Pay with Traditional or Crypto Currency'),
            'BLEUMI_DESCRIPTION' => Configuration::get('BLEUMI_DESCRIPTION', 'PayPal, Credit/Debit Card, Algorand, USD Coin, Celo, Celo Dollar, R-BTC, Dollar on Chain.'),
        );
    }

    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    public function hookPayment($params)
    {
        if (!$this->active) {
            return;
        }
        
        $this->smarty->assign('module_dir', $this->_path);
        return $this->display(__FILE__, 'views/templates/hook/payment.tpl');
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        
        $method_logo = Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/bleumi_100.png');
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText(Configuration::get('BLEUMI_TITLE'))
        ->setLogo($method_logo)
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation('<p>' . Configuration::get('BLEUMI_DESCRIPTION') . '</p>');
        
        return [
            $option
        ];
    }
}
