<?php

use PrestaShop\PrestaShop\Adapter\Presenter\Order\OrderPresenter;

class b2b_storeaccountModuleFrontController extends ModuleFrontController
{

    public $order_presenter;


    public function checkAccess()
    {
        if (!$this->context->customer->isLogged()) {
            $this->redirect_after = 'index';
            $this->redirect();
        } else if(!$this->module->isB2BCustomer($this->context->customer->id)){
            $this->redirect_after = 'my-account';
            $this->redirect();
        }

        return parent::checkAccess();
    }

    public function initContent()
    {
        if ($this->order_presenter === null) {
            $this->order_presenter = new OrderPresenter();
        }

        parent::initContent();

        $this->context->smarty->assign(array_merge($this->module->getCreditVariables(), [
            'list_orders' => $this->getTemplateVarOrders()
        ]));

        $this->setTemplate('module:'.$this->module->name.'/views/templates/front/account.tpl');
    }

    public function getTemplateVarOrders()
    {
        $orders = [];
        $customer_orders = $this->module->getCustomerUnpaidOrders($this->context->customer->id);
        foreach ($customer_orders as $customer_order) {
            $order = new Order((int) $customer_order['id_order']);
            $orders[$customer_order['id_order']] = $this->order_presenter->present($order);
        }

        return $orders;
    }

    public function getTemplateVarPage()
    {
        $vars = parent::getTemplateVarPage();
        // $vars['meta']['title'] = $this->trans('Rejestracja B2B');
        return $vars;
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();

        $breadcrumb['links'][] = [
            'title' => $this->trans('Kredyt kupiecki', [], 'Modules.B2BStore.Shop'),
            'url' => $this->context->link->getModuleLink($this->module->name, 'account'),
        ];

        return $breadcrumb;
    }

    
}
