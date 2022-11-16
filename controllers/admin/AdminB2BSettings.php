<?php

class AdminB2BSettingsController extends ModuleAdminController
{

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();
    }

    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = $this->trans('You do not have permission to view this.', [], 'Admin.Notifications.Error');

            return;
        }

        $this->content .= $this->renderForm();

        $this->context->smarty->assign([
            'content' => $this->content,
        ]);
    }

    public function getFieldsValues()
    {
        return [
            'B2B_NOTIFICATION_EMAIL' => Tools::getValue('B2B_NOTIFICATION_EMAIL', Configuration::get('B2B_NOTIFICATION_EMAIL')),
            'B2B_DEFAULT_GROUP' => Tools::getValue('B2B_DEFAULT_GROUP', Configuration::get('B2B_DEFAULT_GROUP')),
            'B2B_PAYMENT_STATE' => Tools::getValue('B2B_PAYMENT_STATE', Configuration::get('B2B_PAYMENT_STATE')),
            'B2B_PAYMENT_CANCEL' => Tools::getValue('B2B_PAYMENT_CANCEL', Configuration::get('B2B_PAYMENT_CANCEL')),
        ];
    }
    
    public function renderForm()
    {

        $this->fields_value = $this->getFieldsValues();

        $this->fields_form = [
            'legend' => [
                'title' => $this->trans('Settings', [], 'Admin.Global'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->trans('Adres e-mail dla powiadomień', [], 'Modules.B2BStore.Admin'),
                    'name' => 'B2B_NOTIFICATION_EMAIL',
                    'class' => 'fixed-width-xxl',
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Grupa klientów B2B', [], 'Modules.B2BStore.Admin'),
                    'name' => 'B2B_DEFAULT_GROUP',
                    'class' => 'fixed-width-xxl',
                    'options' => [
                        'query' => Group::getGroups($this->context->language->id),
                        'id' => 'id_group',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Status zamówienia opłaconego kredytem', [], 'Modules.B2BStore.Admin'),
                    'name' => 'B2B_PAYMENT_STATE',
                    'class' => 'fixed-width-xxl',
                    'options' => [
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->trans('Status anulowanego zamówienia', [], 'Modules.B2BStore.Admin'),
                    'name' => 'B2B_PAYMENT_CANCEL',
                    'class' => 'fixed-width-xxl',
                    'options' => [
                        'query' => OrderState::getOrderStates($this->context->language->id),
                        'id' => 'id_order_state',
                        'name' => 'name',
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->trans('Save', [], 'Admin.Actions'),
            ]
        ];
 
        $this->fields_form['submit'] = [
            'title' => $this->trans('Save'),
            'name' => 'b2bConfigSubmit',
            'class' => 'btn btn-default pull-right'
        ];

        $this->tpl_vars = array(
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return parent::renderForm();
        
    }

    public function postProcess()
    {
        if (Tools::isSubmit('b2bConfigSubmit')) {
            $email = Tools::getValue('B2B_NOTIFICATION_EMAIL');
            if (!Validate::isEmail($email)) {
                $this->errors[] = $this->trans('Podano niepoprawny adres e-mail.', [], 'Modules.B2BStore.Admin');
            }

            if (!count($this->errors)) {
                Configuration::updateValue('B2B_NOTIFICATION_EMAIL', $email);
                Configuration::updateValue('B2B_DEFAULT_GROUP', Tools::getValue('B2B_DEFAULT_GROUP'));
                Configuration::updateValue('B2B_PAYMENT_STATE', Tools::getValue('B2B_PAYMENT_STATE'));
                Configuration::updateValue('B2B_PAYMENT_CANCEL', Tools::getValue('B2B_PAYMENT_CANCEL'));
                $this->confirmations[] = $this->trans('Ustawienia zostały zaktualizowane.', [], 'Modules.B2BStore.Admin');
                // $this->_clearCache(); TODO
            } else {
                return false;
            }
        }

        return true;
    }
}