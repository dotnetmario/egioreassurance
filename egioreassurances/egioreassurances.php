<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once __DIR__.'/classes/Reassurances.php';

class EgioReassurances extends Module
{
    public function __construct()
    {
        $this->name = 'egioreassurances';
        $this->tab = 'front_office_features';
        $this->version = '1.0.0';
        $this->author = 'Omar ben';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.7',
            'max' => _PS_VERSION_
        ];
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Egio Reassurances');
        $this->description = $this->l('Displays custom view in the front');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        if (!Configuration::get('ELEMENTS_LIMIT') || !Configuration::get('ICON_DIMENTION_X') || !Configuration::get('ICON_DIMENTION_Y')) {
            $this->warning = $this->l('Configuration values not set!');
        }
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
    
        if (!parent::install() 
            || !Configuration::updateValue('ELEMENTS_LIMIT', 10) 
            || !Configuration::updateValue('ICON_DIMENTION_X', 40) 
            || !Configuration::updateValue('ICON_DIMENTION_Y', 40)
            || !$this->installDB()
            || !$this->installTab()
            || !$this->registerHook("displayFooterBefore")
        ) {
            return false;
        }
    
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall() 
            || !Configuration::deleteByName('ELEMENTS_LIMIT') 
            || !Configuration::deleteByName('ICON_DIMENTION_X') 
            || !Configuration::deleteByName('ICON_DIMENTION_Y')
            || !$this->dropDB()
            || !$this->uninstallTab()
        ) {
            return false;
        }

        return true;
    }

    public function hookDisplayFooterBefore(){
        $reassurs = Reassurances::getReassurance();

        // return cols size for bootstrap css grid
        $cols = (int)floor(12 / count($reassurs));
        $icon_width = Configuration::get('ICON_DIMENTION_X');
        $icon_hight = Configuration::get('ICON_DIMENTION_Y');
        $img_basepath = $this->_path.'views/img/';


        $this->context->smarty->assign(array(
            'reassurs' => $reassurs,
            'cols' => $cols,
            'icon_width' => $icon_width,
            'icon_hight' => $icon_hight,
            'img_basepath' => $img_basepath
        ));

        return $this->display(__FILE__, 'egioreassurances.tpl');
    }


    public function getContent()
    {
        $output = null;

        if (Tools::isSubmit('submit'.$this->name)) {
            $elements_limit = strval(Tools::getValue('ELEMENTS_LIMIT'));
            $icon_dim_x = strval(Tools::getValue('ICON_DIMENTION_X'));
            $icon_dim_y = strval(Tools::getValue('ICON_DIMENTION_Y'));

            if (   (empty($elements_limit) || !Validate::isInt($elements_limit))
                || (empty($icon_dim_x) || !Validate::isInt($icon_dim_x))
                || (empty($icon_dim_y) || !Validate::isInt($icon_dim_y))
            ) {
                $output .= $this->displayError($this->l('Invalid Configuration value'));
            } else {
                Configuration::updateValue('ELEMENTS_LIMIT', $elements_limit);
                Configuration::updateValue('ICON_DIMENTION_X', $icon_dim_x);
                Configuration::updateValue('ICON_DIMENTION_Y', $icon_dim_y);

                $output .= $this->displayConfirmation($this->l('Settings updated'));
            }
        }

        return $output.$this->displayForm();
    }

    public function displayForm()
    {
        // Get default language
        $defaultLang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fieldsForm[0]['form'] = [
            'legend' => [
                'title' => $this->l('Settings'),
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Limit of elements to display (must be number default 10)'),
                    'name' => 'ELEMENTS_LIMIT',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Dimention X of the icon in pixels (must be number default 40)'),
                    'name' => 'ICON_DIMENTION_X',
                    'size' => 20,
                    'required' => true
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Dimention Y of the icon in pixels (must be number default 40)'),
                    'name' => 'ICON_DIMENTION_Y',
                    'size' => 20,
                    'required' => true
                ]
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            ]
        ];

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

        // Language
        $helper->default_form_language = $defaultLang;
        $helper->allow_employee_form_lang = $defaultLang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit'.$this->name;
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => AdminController::$currentIndex.'&configure='.$this->name.'&save'.$this->name.
                '&token='.Tools::getAdminTokenLite('AdminModules'),
            ],
            'back' => [
                'href' => AdminController::$currentIndex.'&token='.Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            ]
        ];

        // Load current value
        $helper->fields_value['ELEMENTS_LIMIT'] = Tools::getValue('ELEMENTS_LIMIT', Configuration::get('ELEMENTS_LIMIT'));
        $helper->fields_value['ICON_DIMENTION_X'] = Tools::getValue('ICON_DIMENTION_X', Configuration::get('ICON_DIMENTION_X'));
        $helper->fields_value['ICON_DIMENTION_Y'] = Tools::getValue('ICON_DIMENTION_Y', Configuration::get('ICON_DIMENTION_Y'));

        return $helper->generateForm($fieldsForm);
    }


    public function installDB()
    {
        $return = true;
        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'reassurance_elements` (
                `id_reassurance_elements` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `icon` VARCHAR(255),
                `statuts` TINYINT NOT NULL,
                `position` INT NOT NULL,
                `link` VARCHAR(250) NOT NULL,
                `new_tab` TINYINT DEFAULT 0 NOT NULL,
                `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                `updated_at` DATETIME ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id_reassurance_elements`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'reassurance_elements_shop` (
                `id_reassurance_elements` INT(10) UNSIGNED NOT NULL,
                `id_shop` INT(10) UNSIGNED NOT NULL,
                PRIMARY KEY (`id_reassurance_elements`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        $return &= Db::getInstance()->execute('
                CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'reassurance_elements_lang` (
                `id_reassurance_elements` INT UNSIGNED NOT NULL,
                `id_shop` INT(10) UNSIGNED NOT NULL,
                `id_lang` INT(10) UNSIGNED NOT NULL ,

                `libelle` VARCHAR(60) NOT NULL,
                `description` text NOT NULL,
                `alt` VARCHAR(100),
                `link_title` VARCHAR(100),
                PRIMARY KEY (`id_reassurance_elements`, `id_lang`, `id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 ;'
        );

        return $return;
    }

    public function dropDB(){
        $return = true;

        $return &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'reassurance_elements`');
        $return &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'reassurance_elements_shop`');
        $return &= Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'reassurance_elements_lang`');

        return $return;
    }

    private function installTab()
    {
        $tab = new Tab();
        $tab->class_name = "AdminElements";
        $tab->module = $this->name;
        $tab->active = true;
        $tab->id_parent = (int)Tab::getIdFromClassName('ShopParameters');
        $tab->name = array();

        foreach (Language::getLanguages() as $lang) {
            $tab->name[$lang['id_lang']] = $this->trans('Reassurance elements', array(), 'Modules.egioreassurances.Admin', $lang['locale']);
        }


        return $tab->add();
    }

    private function uninstallTab()
    {
        $tabId = (int) Tab::getIdFromClassName('AdminElements');

        
        if (!$tabId) {
            return true;
        }

        $tab = new Tab($tabId);

        return $tab->delete();
    }
}