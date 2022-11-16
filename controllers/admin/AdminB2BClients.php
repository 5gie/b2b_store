<?php

class AdminB2BClientsController extends ModuleAdminController
{

    protected $position_identifier = 'position';

    public function __construct()
    {
        $this->table = 'customer';
        $this->className = 'Customer';
        $this->deleted = false;
        $this->module = 'b2b_store';
        $this->explicitSelect = false;
        $this->_defaultOrderBy = 'id_customer';
        $this->allow_export = false;
        $this->_defaultOrderWay = 'DESC';
        $this->bootstrap = true;
        $this->list_no_link = true;

        if (Shop::isFeatureActive()){
            Shop::addTableAssociation($this->table, array('type' => 'shop'));
        }

        $this->_select = 'a.*, CONCAT(a.firstname, " ", a.lastname) as customer_name';
        $this->_where = 'AND a.is_b2b = 1 AND a.deleted = 0';

        parent::__construct();

        $this->fields_list = array(
            'id_customer' => array(
                'title' => $this->trans('ID', [], 'Modules.B2BStore.Admin'),
                'class' => 'fixed-width-xs',
            ),
            'customer_name' => array(
                'title' => $this->trans('Imię i Nazwisko', [], 'Modules.B2BStore.Admin'),
            ),
            'email' => array(
                'title' => $this->trans('E-mail', [], 'Modules.B2BStore.Admin'),
            ),
            'company' => array(
                'title' => $this->trans('Firma', [], 'Modules.B2BStore.Admin'),
            ),
            'siret' => array(
                'title' => $this->trans('NIP', [], 'Modules.B2BStore.Admin'),
            ),
            'date_add' => array(
                'title' => $this->trans('Zarejestrowany', [], 'Modules.B2BStore.Admin'),
            ),
            'id_default_group' => array(
                'title' => $this->trans('B2B', [], 'Modules.B2BStore.Admin'),
                'callback' => 'isB2BCustomer',
                'class' => 'fixed-width-xs',
                'search' => false
            ),
        );
     
    }

    public function isB2BCustomer($id_group_default, $row)
    {
        return $this->module->isB2BCustomer($row['id_customer'])? '<i class="icon-check" style="color: #72c279"></i>' : '<i class="icon-remove" style="color:#e08f95"></i>';
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addCss('/modules/' . $this->module->name . '/views/assets/css/admin.css');
    }

    public function renderList()
    {
        $this->addRowAction('details');
        return parent::renderList();
    }

    public function initToolbar()
    {
        parent::initToolbar();
        unset($this->toolbar_btn['new']);
    }

    public function redirectList()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminB2BClients'));
    }

    public function renderDetails()
    {
        if(!$id_customer = Tools::getValue('id_customer')){
            $this->redirectList();
        }

        $customer = new Customer($id_customer);

        if(!$customer->id || !$this->module->isB2BCustomer($id_customer)){
            $this->redirectList();
        }

        return $this->renderCustomerInfo($customer) . $this->renderCustomerOrdersList($id_customer);
    }

    public function renderCustomerOrdersList($id_customer)
    {

        $orders = Order::getCustomerOrders($id_customer); // TODO: add limit for this

        $fields_list = $this->getCustomerOrdersFieldList();

        $helper = new HelperList();
        $helper->list_id = 'customer-orders';
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = [];
        $helper->show_toolbar = false;
        $helper->module = $this->module;
        $helper->listTotal = Order::getCustomerNbOrders($id_customer);
        $helper->identifier = 'id_order';
        $helper->title = $this->trans('Ostatnie zamówienia klienta', [], 'Modules.B2BStore.Admin');
        $helper->token = Tools::getAdminTokenLite('AdminB2BClients');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->no_link = true;

        return $helper->generateList($orders, $fields_list);
    }

    public function getCustomerOrdersFieldList()
    {
        return [
            'id_order' => [
                'title' => $this->trans('ID', [], 'Modules.B2BStore.Admin'),
                'type' => 'text',
                'search' => false,
            ],
            'reference' => [
                'title' => $this->trans('Nr. ref', [], 'Modules.B2BStore.Admin'),
                'type' => 'text',
                'search' => false,
            ],
            'payment' => [
                'title' => $this->trans('Płatność', [], 'Modules.B2BStore.Admin'),
                'type' => 'text',
                'search' => false,
            ],
            'order_state' => [
                'title' => $this->trans('Status', [], 'Modules.B2BStore.Admin'),
                'type' => 'text',
                'search' => false,
                'callback' => 'orderStateColor',
            ],
            'total_paid_tax_excl' => [
                'title' => $this->trans('Suma netto', [], 'Modules.B2BStore.Admin'),
                'type' => 'price',
                'search' => false,
            ],
            'total_paid' => [
                'title' => $this->trans('Suma brutto', [], 'Modules.B2BStore.Admin'),
                'type' => 'price',
                'search' => false,
            ],
        ];
    }

    public function orderStateColor($state, $row = false)
    {
        return '<span class="badge rounded badge-print-light" style="background:'.$row['order_state_color'].'">' . $state . '<span>';
    }

    public function renderCustomerInfo(Customer $customer)
    {
        $unpaid_orders = (int) $this->module->getCustomerUnpaidOrdersAmount($customer->id);
        $credit = $customer->credit;
        $credit_remaining_amount = $credit - $unpaid_orders;
        $stats = $customer->getStats();
        
        if ($stats) {
            $stats['total_orders'] = $this->context->currentLocale->formatPrice((int) $stats['total_orders'], $this->context->currency->iso_code);
        }
        
        $currency = Currency::getCurrencyInstance((int) $customer->id_currency);
        if(!Validate::isLoadedObject($currency)){
            $currency = Currency::getDefaultCurrency();
        }
        $customer_currency = $currency->iso_code;

        $this->context->smarty->assign([
            'credit' => $this->context->currentLocale->formatPrice($credit, $this->context->currency->iso_code),
            'unpaid_orders' => $this->context->currentLocale->formatPrice($unpaid_orders, $this->context->currency->iso_code),
            'credit_remaining_amount' => $this->context->currentLocale->formatPrice($credit_remaining_amount, $this->context->currency->iso_code),
            'min_order_amount' => $this->context->currentLocale->formatPrice($customer->min_order_amount, $this->context->currency->iso_code),
            'customer_currency' => $customer_currency,
            'customer_stats' => $stats,
            'is_b2b' => $this->module->isB2BCustomer($customer->id),
            'customer' => get_object_vars($customer)
        ]);

        return $this->context->smarty->fetch('module:' . $this->module->name . '/views/templates/admin/customer-info.tpl');
    }

    public function initPageHeaderToolbar()
    {
        if ($this->display == 'details') {
            // Default cancel button - like old back link
            $back = Tools::safeOutput(Tools::getValue('back', ''));
            if (empty($back)) {
                $back = self::$currentIndex . '&token=' . $this->token;
            }
            if (!Validate::isCleanHtml($back)) {
                die(Tools::displayError());
            }
            if (!$this->lite_display) {
                $this->page_header_toolbar_btn['list'] = [
                    'href' => $back,
                    'desc' => $this->trans('Back to list', [], 'Admin.Actions'),
                    'icon' => 'process-icon-back'
                ];
            }
        }

        parent::initPageHeaderToolbar();

    }

}
