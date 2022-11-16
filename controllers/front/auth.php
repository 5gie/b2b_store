<?php

class b2b_storeauthModuleFrontController extends ModuleFrontController
{

    private $registerForm;
    private $loginForm;

    public function checkAccess()
    {
        if ($this->context->customer->isLogged() && !$this->ajax) {
            $this->redirect_after = ($this->authRedirection) ? urlencode($this->authRedirection) : 'my-account';
            $this->redirect();
        }

        return parent::checkAccess();
    }

    public function init()
    {
        parent::init();
        $this->initForms();
    }

    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:'.$this->module->name.'/views/templates/front/auth.tpl');
    }

    public function initForms()
    {
        $this->registerForm = $this->makeCustomerForm();
        $this->loginForm = $this->makeLoginForm();

        $this->registerForm->fillFromCustomer(new Customer());

        $this->sendNotificationMail($this->context->customer);

        $this->context->smarty->assign([
            'register_form' => $this->registerForm,
            'login_form' => $this->loginForm,
            'guest_allowed' => Configuration::get('PS_GUEST_CHECKOUT_ENABLED'),
            'show_login_form' => (bool) !Tools::getIsset('register'),
            'register_url' => $this->context->link->getModuleLink($this->module->name, 'auth', ['register' => 1]),
            'login_url' => $this->context->link->getModuleLink($this->module->name, 'auth')
        ]);
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitCreate')) {
            $this->registerForm->fillWith(Tools::getAllValues());
            if($this->registerForm->submit()){
                
                $this->success[] = $this->trans('Dziękujemy za rejestracje w serwisie Mondex B2B. Twoje konto zostanie zweryfikowane najszybciej jak to możliwe. Potwierdzenie zostanie wysłane na podany przez Ciebie adres mailowy.', [], 'Module.B2B.Front');
                // $this->sendNotificationMail($this->registerForm->getCustomer());
                $this->redirectWithNotifications('index');
            }
        } elseif(Tools::isSubmit('submitLogin')) {
            $this->loginForm->fillWith(Tools::getAllValues());
            if ($this->loginForm->submit()) {
                $this->redirect();
            }
        }
    }

    public function getTemplateVarPage()
    {
        $vars = parent::getTemplateVarPage();
        // $vars['meta']['title'] = $this->trans('Rejestracja B2B');
        return $vars;
    }

    public function setMedia()
    {
        parent::setMedia();
        // $this->context->controller->registerStylesheet('blog-css', '/modules/' . $this->module->name . '/views/assets/css/blog.css', ['media' => 'all', 'priority' => 0]);
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        // $breadcrumb['links'][] = [
        //     'title' => $this->trans('Nasze poradniki'),
        //     'url' => $this->context->link->getModuleLink($this->module->name, 'list')
        // ];

        return $breadcrumb;
    }

    // TODO: test if it work
    public function sendNotificationMail(Customer $customer): void
    {
        try{
            Mail::Send(
                $this->context->language->id,
                'office',
                $this->trans('Nowa wiadomość z hurtowni B2B Mondex', [], 'Modules.B2BStore.Shop'),
                $this->getMessageVars($customer),
                // Configuration::get('B2B_NOTIFICATION_EMAIL'),
                'k.chmielewski@sellision.com', // TODO: change this
                null,
                $customer->email,
                null,
                null,
                null,
                dirname(__FILE__) . '/mails/',
                false,
                null,
                null,
                null
            );
        } catch(\Exception $e){
            PrestaShopLogger::addLog($e->getMessage(), PrestaShopLogger::LOG_SEVERITY_LEVEL_ERROR, 'null', 'customer', $customer->id);
        }

    }    

    public function getMessageVars($customer){
        
        return [
            '{id_customer}' => $customer->id,
            '{lastname}' => $customer->lastname,
            '{firstname}' => $customer->firstname,
            '{email}' => $customer->email,
            '{company}' => $customer->company,
            '{siret}' => $customer->siret,
        ];

    }

}
