<?php
class Product extends ProductCore
{
    public $qty_pack;

    public function __construct($id_product = null, $full = false, $id_lang = null, $id_shop = null, Context $context = null)
    {
        self::$definition['fields']['qty_pack'] = ['type' => self::TYPE_INT, 'validate' => 'isInt'];

        parent::__construct($id_product, $full, $id_lang, $id_shop, $context);
    }

    public static function initPricesComputation($id_customer = null)
    {
        if ((int) $id_customer > 0) {
            $customer = new Customer((int) $id_customer);
            if (!Validate::isLoadedObject($customer)) {
                die(Tools::displayError());
            }
            self::$_taxCalculationMethod = Group::getPriceDisplayMethod((int) $customer->id_default_group);
            $cur_cart = Context::getContext()->cart;
            $id_address = 0;
            if (Validate::isLoadedObject($cur_cart)) {
                $id_address = (int) $cur_cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
            }
            $address_infos = Address::getCountryAndState($id_address);

            if (
                self::$_taxCalculationMethod != PS_TAX_EXC
                && !empty($address_infos['vat_number'])
                && $address_infos['id_country'] != Configuration::get('VATNUMBER_COUNTRY')
                && Configuration::get('VATNUMBER_MANAGEMENT')
            ) {
                self::$_taxCalculationMethod = PS_TAX_EXC;
            }

            if ($module = Module::getInstanceByName('b2b_Store')) {
                if ($module->isB2BCustomer($id_customer)) {
                    $tax_display_method = Context::getContext()->cookie->tax_display_method;
                    if ($tax_display_method != self::$_taxCalculationMethod) {
                        self::$_taxCalculationMethod = $tax_display_method;
                    }
                }
            }
        } else {
            self::$_taxCalculationMethod = Group::getPriceDisplayMethod(Group::getCurrent()->id);
        }
    }
}
