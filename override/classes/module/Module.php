<?php
abstract class Module extends ModuleCore {
    protected function getCacheId($name = null)
    {
        $cache_id = parent::getCacheId($name);
        $module = parent::getInstanceByName('b2b_store');
        if ($module) {
            if ($module->isB2BCustomer($this->context->customer->id)) {
                $cache_id .= '|' . (int) $this->context->customer->id;
                $cache_id .= '|' . (int) Product::getTaxCalculationMethod($this->context->customer->id);
            }
        }
        return $cache_id;
    }
}