<?php

class b2b_storeupdaterModuleFrontController extends ModuleFrontController
{

    public function checkAccess()
    {
        if (!$this->context->customer->isLogged()) {
            $this->redirect_after = 'index';
            $this->redirect();
        } else if (!$this->module->isB2BCustomer($this->context->customer->id)) {
            $this->redirect_after = 'my-account';
            $this->redirect();
        }

        return parent::checkAccess();
    }

    public function postProcess()
    {

        if (Tools::getValue('action') == 'change-tax-display-method') {

            $tax_display_method = Tools::getValue('method', Product::getTaxCalculationMethod($this->context->customer->id));
            $this->context->cookie->tax_display_method = $tax_display_method;
            // $this->redirect_after = $this->context->controller->php_self;
            
        }
        $this->redirect();

    }

}
