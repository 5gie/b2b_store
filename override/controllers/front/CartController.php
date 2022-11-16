<?php

class CartController extends CartControllerCore {

    public function updateCart()
    {
        parent::updateCart();
        if ($this->context->customer->id && $module = Module::getInstanceByName('b2b_store')) {
            if(!$module->checkB2BCredit($this->context->customer->id)){
                $this->errors[] = $this->trans('Twój kredyt kupiecki został przekroczony, dalsza możliwość zakupów będzie możliwa po spłacie zobowiązania.', [], 'Modules.B2BStore.Shop');
            }
        }
    }

    public function processChangeProductInCart()
    {
        if ($this->qty == 1 && !Tools::getIsset('qty') && $module = Module::getInstanceByName('b2b_store')) {
            $qty_pack = $module->getProductQtyPack($this->id_product);
            if ($qty_pack > 1) {
                $this->qty = $qty_pack;
            }
        }

        return parent::processChangeProductInCart();
    }

}