<?php

class OrderController extends OrderControllerCore
{
    public function initContent()
    {
        parent::initContent();
        if ($this->context->customer->id && $module = Module::getInstanceByName('b2b_store')) {
            if (!$module->checkB2BCredit($this->context->customer->id)) {
                $cartLink = $this->context->link->getPageLink('cart', null, null, ['action' => 'show']);
                Tools::redirect($cartLink);
            }
        }
    }
}