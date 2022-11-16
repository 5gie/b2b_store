<?php

use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class b2b_store extends PaymentModule
{

    // private $templateFile;
    protected array $module_tabs = [];
    protected bool $dev = false;

    public function __construct()
    {
        $this->name = 'b2b_store';
        $this->tab = 'others';
        $this->author = 'Sellision';
        $this->version = '1.0.0';
        $this->need_instance = 0;
        $this->dev = true;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('B2B store', [], 'Modules.B2BStore.Admin');
        // TODO
        $this->description = $this->trans('Credit, Min. order amount, Customer currency, net/gross switch, B2B auth', [], 'Modules.B2BStore.Admin');

        $this->ps_versions_compliancy = array('min' => '1.7.8', 'max' => _PS_VERSION_);

        $this->templateFile = 'module:b2b_store/views/templates/hook/product.tpl';

        $this->module_tabs = [
            [
                'controller' => 'AdminB2BDashboard',
                'name' => 'B2B',
                'icon' => 'cached',
                'is_parent' => true
            ],
            [
                'controller' => 'AdminB2BClients',
                'name' => 'Kontrahenci',
                'icon' => '',
                'is_parent' => false
            ],
            [
                'controller' => 'AdminB2BSettings',
                'name' => 'Ustawienia',
                'icon' => '',
                'is_parent' => false
            ]
        ];
    }

    public function install()
    {
        return 
            parent::install() &&
            $this->installSql() && 
            $this->updateConfigB2B(true) &&
            $this->registerTabs() &&
            $this->registerHook([
                'additionalCustomerFormFields',
                'displayAdminProductsMainStepRightColumnBottom',
                'actionGetProductPropertiesAfter',
                'actionCustomerLogoutAfter',
                'displayPaymentReturn',
                'paymentOptions',
                'displayCustomerAccount',
                'displayHeader',
                'displayProductActions',
                'displayTop',
                'actionAuthentication',
                'actionFrontControllerInitAfter',
                'overrideMinimalPurchasePrice',
                'moduleRoutes'
            ]);
    }

    public function uninstall()
    {
        return parent::uninstall() && $this->uninstallSql() && $this->updateConfigB2B(false) && $this->insertOrderState() && $this->unregisterTabs();
    }

    public function updateConfigB2B($b2b)
    {
        return Configuration::updateValue('PS_B2B_ENABLE', $b2b);
    }

    public function registerTabs()
    {
        $output = true;
        $languages = Language::getLanguages(false);
        $id_parent = Tab::getIdFromClassName('IMPROVE');

        foreach($this->module_tabs as $data){

            $tab = new Tab();
            $tab->class_name = $data['controller'];
            $tab->module = $this->name;
            $tab->id_parent = $id_parent;
            $tab->icon = $data['icon'];
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = $data['name'];
            }
            $output &= $tab->save();

            if($data['is_parent'] && $output){
                $id_parent = $tab->id;
            }

        }

        return $output;
       
    }

    public function unregisterTabs()
    {
        $output = true;
        foreach ($this->module_tabs as $data) {
            $tab = new Tab((int) Tab::getIdFromClassName($data['controller']));
            $output &= $tab->delete();
        }
        return $output;
    }


    /**
     * @return boolean
     */
    protected function installSql()
    {
        return true;
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "customer` ADD is_b2b TINYINT(1) DEFAULT '0';";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` ADD id_currency INT(11) NULL DEFAULT NULL;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` ADD min_order_amount decimal(20,6) NOT NULL DEFAULT 0.000000;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` ADD credit decimal(20,6) NOT NULL DEFAULT 0.000000;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "product` ADD qty_pack INT(30) DEFAULT '0';";

        return Db::getInstance()->execute($sql);
    }

    /**
     * @return boolean
     */
    protected function uninstallSql()
    {
        return true;
        $sql = "ALTER TABLE `" . _DB_PREFIX_ . "customer` DROP is_b2b;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` DROP id_currency;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` DROP min_order_amount;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "customer` DROP credit;";
        $sql .= " ALTER TABLE `" . _DB_PREFIX_ . "product` DROP qty_pack;";

        return Db::getInstance()->execute($sql);
    }

    protected function insertOrderState()
    {
        $sql = new DbQuery();
        $sql->select('id_order_state');
        $sql->from('order_state');
        $sql->where('module_name = "'.$this->name.'"');

        $orderStateExists = Db::getInstance()->getRow($sql->build());
        if ($orderStateExists) {
            $id_state = (int)$orderStateExists['id_order_state'];
        } else {
            $orderState = new OrderState();
            foreach (Language::getLanguages() as $language) {
                $orderState->name[$language['id_lang']] = $this->trans('Płatność kredytem kupieckim', [], 'Modules.B2BStore.Admin');
            }
            $orderState->template = 'b2b_payment';
            $orderState->unremovable = 1;
            $orderState->color = '#716758';
            $orderState->paid = false;
            $orderState->invoice = false;
            $orderState->module_name = $this->name;
            $orderState->add();
            $id_state = (int)$orderState->id;
        }

        Configuration::updateValue('B2B_PAYMENT_STATE', $id_state);

        return true;
    }


    public function hookAdditionalCustomerFormFields(array $params)
    {
        if(!$this->context->controller instanceof b2b_storeauthModuleFrontController){
            // TODO: if they want do display form fields
            // if ($this->context->controller->php_self == 'identity') {
            //     dump($params['fields']);
            // } else {
                unset($params['fields']['siret']);
                unset($params['fields']['company']);
            // }
        } else {
            if(isset($params['fields']['siret'])){
                $params['fields']['siret']->setRequired(true);
            }
            if(isset($params['fields']['company'])){
                $params['fields']['company']->setRequired(true);
            }
            return [
                (new FormField())->setName('is_b2b')->setType('hidden')->setValue(1)
            ];
        }
    }

    public function hookModuleRoutes($params)
    {

        $output = [];

        foreach (Language::getLanguages(false) as $lang) {

            $output['module-' . $this->name . '-auth-' . $lang['id_lang']] = [
                'controller' =>  'auth',
                'rule' => 'rejestracja-b2b',
                'keywords' => [],
                'params' => [
                    'fc' => 'module',
                    'module' => $this->name
                ]
            ];

        }

        return $output;
    }

    public function hookActionCustomerBeforeUpdateGroup(array $params)
    {
        $customer = new Customer((int) $params['id_customer']);
        
        if(empty(array_diff($params['groups'], $customer->getGroups()))){
            return; // if there are no difference between new and old groups
        }

        if(!$id_group = $this->getDefaultB2BGroup()){
            return;  // if there are no b2b group
        }

        if(in_array($id_group, $params['groups'])){
            $this->sendClientNotificationMail($customer->firstname, $customer->email);
        } else {
            return; // there are no b2b group update
        }
    }

    public function sendClientNotificationMail($firstname, $email)
    {
        Mail::Send(
            $this->context->language->id,
            'customer',
            $this->trans('Nowa wiadomość z hurtowni B2B Mondex', [], 'Modules.B2BStore.Shop'),
            [
                '{firstname}' => $firstname,
                '{email}' => $email
            ],
            $email, 
            null,
            Configuration::get('B2B_NOTIFICATION_EMAIL'), // TODO: add configuration for this email
            null,
            null,
            null,
            dirname(__FILE__) . '/mails/',
            false,
            null,
            null,
            null
        );
    }

    public function hookDisplayAdminProductsMainStepRightColumnBottom(array $params)
    {
        $this->context->smarty->assign([
            'qty_pack' => $this->getProductQtyPack((int)$params['id_product'])
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/admin/qty-pack.tpl');
    }

    public function getProductQtyPack(int $id_product)
    {
        return Db::getInstance()->getValue('SELECT qty_pack FROM `'._DB_PREFIX_.'product` WHERE id_product = '.$id_product);
    }

    public function hookActionGetProductPropertiesAfter(array $params)
    {
        // TODO: Do i need this if i already overrided Product.php?
        if(!isset($params['product']['id_product'])){
            return;
        }
        // $params['product']['qty_pack'] = $this->getProductQtyPack((int) $params['product']['id_product']);
        if(isset($params['product']['quantity_wanted'])){
            $quantity_wanted = $params['product']['quantity_wanted'];
            $quantity_pack = $this->getProductQtyPack((int) $params['product']['id_product']);
            if($params['product']['quantity'] > $quantity_pack && $quantity_wanted < $quantity_pack){
                $params['product']['quantity_wanted'] = $quantity_pack;
            }
        }
    }

    /**
     * We are looking for an input[name="product-quantity-spin"] and add a qty-pack to it
     * It doesnt work in ajax refresh
     */
    // public function hookActionOutputHTMLBefore($params)
    // {
    //     $params['html'] = preg_replace_callback("/<input[^>]*name\s*=\s*.*?\"(product-quantity-spin)\".*?[^>]+>/i", function ($matches) {

    //         $input = $matches[0];

    //         preg_match('/\bdata-product-id\s*=\s*[\'"](\d)[\'"]/u', $matches[0], $results);

    //         $id_product = false;

    //         if(isset($results[1])){
    //             $id_product = $results[1];
    //         }
            
    //         if(!$id_product){
    //             return $input;
    //         }

    //         if($qty_pack = $this->getProductQtyPack((int) $id_product)){

    //             if($qty_pack == 1){
    //                 return $input;
    //             }
    //             $input = str_replace($results[0], $results[0] . PHP_EOL . ' data-qty-pack="' . $qty_pack . '"', $input);

    //         }

    //         return $input;

    //     }, $params['html']);
    // }

    public function hookActionAuthentication(array $params)
    {
        if(!$this->isB2BCustomer($params['customer']->id)){
            return;
        }
        $currency = Currency::getCurrencyInstance((int) $params['customer']->id_currency);
        
        if(!Validate::isLoadedObject($currency)){
            return;
        }
        $this->context->cookie->id_currency = $currency->id;
        Tools::setCurrency($this->context->cookie);
    }

    public function getDefaultB2BGroup()
    {
        if (!$id_group = Configuration::get('B2B_DEFAULT_GROUP')) {
            if (!$group = Group::searchByName('B2B')) {
                return false;
            } else {
                $id_group = $group['id_group'];
            }
        }
        return $id_group;
    }

    public function isB2BCustomer($id_customer): bool
    {
        $customer = new Customer($id_customer);
        if(!$customer->id){
            return false;
        } else if(!$customer->is_b2b){
            return false;
        } else{

            if(!$b2b = $this->getDefaultB2BGroup()){
                return false;
            } else {
                // if(!in_array($b2b, $customer->getGroups())){ // TODO: check witch solution
                if($b2b != $customer->id_default_group){ 
                    return false;
                }
            }

        }

        return true;
    }

    public function hookActionCustomerLogoutAfter(array $params)
    {

        if(!isset($params['customer']->id)){
            return;
        } else if (!$this->isB2BCustomer($params['customer']->id)) {
            return;
        }

        $currency = Currency::getDefaultCurrency();

        if(Validate::isLoadedObject($currency)){
            if($this->context->cookie->id_currency != $currency->id){
                $this->context->cookie->id_currency = $currency->id;
                Tools::setCurrency($this->context->cookie);
            }
        }

    }

    public function hookOverrideMinimalPurchasePrice(array $params)
    {
        if(!$this->context->customer->id){
            return;
        } else if(!$this->isB2BCustomer($this->context->customer->id)){
            return;
        } else {
            $min_order_amount = (float) $this->context->customer->min_order_amount * $this->context->currency->conversion_rate;
            if($min_order_amount > $params['minimalPurchase']){
                $params['minimalPurchase'] = $min_order_amount;
            }
        }
    }


    public function hookPaymentOptions($params)
    {
        if (!$this->active || !$this->checkCurrency($params['cart']) || !$this->context->customer->id) {
            return [];
        }

        if (!$this->isB2BCustomer((int) $this->context->customer->id)) {
            return [];
        }

        $this->smarty->assign(
            $this->getPaymentVarInfos()
        );

        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
        ->setCallToActionText($this->trans('Zapłać kredytem kupieckim', [], 'Modules.CreditPayment.Shop'))
        ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
        ->setAdditionalInformation($this->fetch('module:b2b_store/views/templates/hook/credit_payment_intro.tpl'));

        $payment_options = [
            $newOption,
        ];


        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        if ($state == Configuration::get('B2B_PAYMENT_STATE')) {
            
            $totalToPaid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();

            $iso_code = (new Currency($params['order']->id_currency))->iso_code;

            $this->smarty->assign(array_merge($this->getCreditVariables(), [
                'total' => $this->context->getCurrentLocale()->formatPrice(
                    $totalToPaid,
                    $iso_code
                ),
                'total_tax_excl' => $this->context->getCurrentLocale()->formatPrice($params['order']->total_paid_tax_excl, $iso_code),
                'status' => 'ok',
                'reference' => $params['order']->reference,
                'contact_url' => $this->context->link->getPageLink('contact', true),
            ]));
        } else {
            $this->smarty->assign(
                [
                    'status' => 'failed',
                    'contact_url' => $this->context->link->getPageLink('contact', true),
                ]
            );
        }

        return $this->fetch('module:b2b_store/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getPaymentVarInfos()
    {
        $presenter = new CartPresenter();
        $presented_cart = $presenter->present($this->context->cart);

        $priceFormatter = new PriceFormatter();

        $credit = $priceFormatter->convertAmount($this->context->customer->credit);
        $credit_amount = $credit - $priceFormatter->convertAmount($this->getCustomerUnpaidOrdersAmount($this->context->customer->id));

        $credit_alert = false;
        if(($credit_amount - $presented_cart['cart']['totals']['total_excluding_tax']['amount'])  < 0){
            $credit_alert = $this->trans('Twój kredyt zostanie przekroczony, możliwość zakupów zostanie zablokowana do momentu spłaty zobowiązania.', [], 'Modules.B2BStore.Shop');
        }

        return [
            'credit' => $this->context->currentLocale->formatPrice($credit, $this->context->currency->iso_code),
            'credit_remaining_amount' => $this->context->currentLocale->formatPrice($credit_amount, $this->context->currency->iso_code),
            'cart_value' => $presented_cart['cart']['totals']['total_excluding_tax']['value'],
            'credit_alert' => $credit_alert
        ];
    }

    public function checkB2BCredit($id_customer)
    {
        if(!$this->isB2BCustomer((int)$id_customer)){
            return true;
        } else if(!$this->context->customer->credit || $this->context->customer->credit === 0){
            return true;
        } else if($this->getCustomerUnpaidOrdersAmount($id_customer) > $this->context->customer->credit){
            return false;
        } else {
            return true;
        }
    }

    /**
     * we are getting the net value of orders which have the status B2B_PAYMENT_STATE in order history 
     * and which do not have the status "paid = 1"
     * TODO: order canceled B2B_PAYMENT_CANCEL
     */
    public function getCustomerUnpaidOrdersAmount($id_customer)
    {
        $sql = 'SELECT SUM(o.total_paid_tax_excl) 
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.id_order IN (
                    SELECT oh.id_order FROM `'._DB_PREFIX_. 'order_history` oh 
                    INNER JOIN  `' . _DB_PREFIX_ . 'order_state` os 
                    ON os.id_order_state = oh.id_order_state AND os.id_order_state = '.(int) Configuration::get('B2B_PAYMENT_STATE'). '
                    WHERE oh.id_order = o.id_order 
                )
                AND o.id_order NOT IN (
                    SELECT oh.id_order FROM `' . _DB_PREFIX_ . 'order_history` oh 
                    INNER JOIN  `' . _DB_PREFIX_ . 'order_state` os 
                    ON os.id_order_state = oh.id_order_state AND os.paid = 1
                    WHERE oh.id_order = o.id_order 
                )
                AND o.id_customer = ' . $id_customer .
                Shop::addSqlRestriction(Shop::SHARE_ORDER);

        return Db::getInstance()->getValue($sql);
    }
    
    public function getCustomerUnpaidOrders($id_customer)
    {
        $sql = 'SELECT o.id_order
                FROM `'._DB_PREFIX_.'orders` o
                WHERE o.id_order IN (
                    SELECT oh.id_order FROM `'._DB_PREFIX_. 'order_history` oh 
                    INNER JOIN  `' . _DB_PREFIX_ . 'order_state` os 
                    ON os.id_order_state = oh.id_order_state AND os.id_order_state = '.(int) Configuration::get('B2B_PAYMENT_STATE'). '
                    WHERE oh.id_order = o.id_order 
                )
                AND o.id_order NOT IN (
                    SELECT oh.id_order FROM `' . _DB_PREFIX_ . 'order_history` oh 
                    INNER JOIN  `' . _DB_PREFIX_ . 'order_state` os 
                    ON os.id_order_state = oh.id_order_state AND os.paid = 1
                    WHERE oh.id_order = o.id_order 
                )
                AND o.id_customer = ' . $id_customer .
                Shop::addSqlRestriction(Shop::SHARE_ORDER);

        return Db::getInstance()->executeS($sql);
    }

    public function hookDisplayCustomerAccount(array $params)
    {

        if(!$this->isB2BCustomer((int) $this->context->customer->id)){
            return;
        }

        $this->smarty->assign([
            'url' => $this->context->link->getModuleLink($this->name, 'account')
        ]);

        return $this->fetch('module:'.$this->name.'/views/templates/hook/account-block.tpl');
    }

    public function getCreditVariables()
    {
        $credit = false;
        $unpaid_orders = false;
        $diff_amount = false;
        $credit_alert = false;

        $priceFormatter = new PriceFormatter();

        if ($this->context->customer->credit != 0) {
            $credit = $priceFormatter->convertAmount($this->context->customer->credit);
            $unpaid_orders = $priceFormatter->convertAmount($this->getCustomerUnpaidOrdersAmount((int) $this->context->customer->id));
            $diff_amount = $credit - $unpaid_orders;
            if ($diff_amount < 0) {
                $credit_alert = $this->trans('Twój kredy został przekroczony, możliwość zamówień została zablokowana do czasu, kiedy uregulujesz zobowiązania.', [], 'Modules.B2BStore.Shop');
            }
        }

        return [
            'credit' => $this->context->currentLocale->formatPrice( $credit, $this->context->currency->iso_code),
            'unpaid_orders' => !empty($unpaid_orders) ? $this->context->currentLocale->formatPrice($unpaid_orders, $this->context->currency->iso_code) : $unpaid_orders,
            'diff_amount' => $diff_amount ? $this->context->currentLocale->formatPrice($diff_amount, $this->context->currency->iso_code) : $diff_amount,
            'credit_alert' => $credit_alert
        ];
    }

    public function hookDisplayProductActions(array $params)
    {
        $cache_id = $this->getCacheId($this->name . '|' . $params['product']->id);
        $template = 'module:' . $this->name . '/views/templates/hook/product-actions.tpl';

        if(!$this->isCached($template, $cache_id)){
            
            if(!isset($params['product']->qty_pack) || !$this->context->customer->id || !$this->isB2BCustomer($this->context->customer->id)){
                return;
            }
    
            $this->context->smarty->assign([
                'qty_pack' => $params['product']->qty_pack == 0 ? 1 : $params['product']->qty_pack
            ]);
        }
        return $this->fetch($template, $cache_id);
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->registerJavascript('b2b-store', '/modules/' . $this->name . '/views/assets/js/front.js');
        $this->context->controller->registerStylesheet('b2b-store', '/modules/' . $this->name . '/views/assets/css/front.css');
    }

    public function hookDisplayTop()
    {
        if(!$this->isB2BCustomer($this->context->customer->id)){
            return;
        }
        $template = 'module:' . $this->name . '/views/templates/hook/tax-select.tpl';
        $current_method = Product::getTaxCalculationMethod($this->context->customer->id);

        $url = $this->context->link->getLanguageLink($this->context->language->id);

        $parsedUrl = parse_url($url);
        $urlParams = [];
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $urlParams);
        }

        $actions = [
            PS_TAX_EXC => [
                'name' => $this->trans('Netto', [], 'Modules.B2BStore.Shop'),
                'url' => sprintf(
                    '%s://%s%s%s?%s',
                    $parsedUrl['scheme'],
                    $parsedUrl['host'],
                    isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '',
                    $parsedUrl['path'],
                    http_build_query(array_merge(
                        $urlParams,
                        [
                            'taxDisplay' => 1,
                            'method' => PS_TAX_EXC
                        ]
                    ))
                )
            ],
            PS_TAX_INC => [
                'name' => $this->trans('Brutto', [], 'Modules.B2BStore.Shop'),
                'url' => sprintf(
                    '%s://%s%s%s?%s',
                    $parsedUrl['scheme'],
                    $parsedUrl['host'],
                    isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '',
                    $parsedUrl['path'],
                    http_build_query(array_merge(
                        $urlParams,
                        [
                            'taxDisplay' => 1,
                            'method' => PS_TAX_INC
                        ]
                    ))
                )
            ]
        ];

        $this->context->smarty->assign([
            'tax_actions' => $actions,
            'current_method' => $current_method
        ]);
        // PS_TAX_EXC
        // PS_TAX_INC
        return $this->fetch($template);
    }

    public function hookActionFrontControllerInitAfter(array $params)
    {
        $this->updateCustomerCurrency();
    }

    public function updateCustomerCurrency()
    {
        if (Tools::isSubmit('taxDisplay')) {
            $this->context->cookie->tax_display_method = Tools::getValue('method', Product::getTaxCalculationMethod($this->context->customer->id));
        }
    }

}
