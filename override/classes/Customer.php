<?php
class Customer extends CustomerCore
{
    public $is_b2b;
    public $id_currency;
    public $min_order_amount;
    public $credit;
    
    public function __construct($id = null)
    {
        self::$definition['fields']['is_b2b'] = ['type' => self::TYPE_BOOL, 'validate' => 'isBool'];
        self::$definition['fields']['id_currency'] = ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'];
        self::$definition['fields']['min_order_amount'] = ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'];
        self::$definition['fields']['credit'] = ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'];
        parent::__construct($id);
    }
}