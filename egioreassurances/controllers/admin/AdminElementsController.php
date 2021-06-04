<?php

class AdminElementsController extends ModuleAdminController
{
    protected $position_identifier = 'id_reassurance_elements';
    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'Reassurances';
        $this->table = 'reassurance_elements';
        $this->identifier = 'id_reassurance_elements';
        $this->lang = true;
        $this->_defaultOrderBy = 'position';
        $this->_defaultOrderWay = 'ASC';

        $this->addRowAction('edit');
        $this->addRowAction('delete');
        Shop::addTableAssociation($this->table, array('type' => 'shop'));

        parent::__construct();

        $this->fields_list = array(
            'icon' => array(
                'title' => $this->l('Icon'),
                'callback' => 'getIcon'
            ),
            'libelle' => array(
                'title' => $this->l('Libellé'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'description' => array(
                'title' => $this->l('Description'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'alt' => array(
                'title' => $this->l('Alt'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'link_title' => array(
                'title' => $this->l('Titre du lien'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'new_tab' => array(
                'title' => $this->l('New tab')
            ),
            'statuts' => array(
                'title' => $this->l('Status'),
            ),
            'position' => array(
                'title' => $this->l('Position'),
                'position' => 'position',
                'align' => 'center',
                'class' => 'fixed-width-md',
            ),
        );
    }

    public function getIcon($val, $row){
        return '<img src="'._MODULE_DIR_.'egioreassurances/views/img/'.$val.'" style="width: 30px; height: 30px;">';
    }

    public function init()
    {
        parent::init();
        
        if(Reassurances::getReassurancesCount() >= (int)Configuration::get('ELEMENTS_LIMIT')){
            unset($this->toolbar_btn['new']);
        }

        if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
            $this->_where = ' AND b.`id_shop` = '.(int)Context::getContext()->shop->id;
        }
    }


    public function postProcess()
    {
        if (Tools::isSubmit('submitAddreassurance_elements')) {
            $_POST['position'] = $this->getPosition();
        }
        if ($this->action && $this->action == 'save' && !empty($_FILES['icon'])) {
            $icon = $this->saveImage('icon');
            if ($icon) {
                $_POST['icon']= $icon;
            }
        }
        return parent::postProcess();
    }

    public function getPosition(){
        $sql = "SELECT MAX(`position`) AS `pos` FROM `"._DB_PREFIX_."reassurance_elements`";

        $res = DB::getInstance()->getRow($sql);

        return (int)$res['pos'] + 1;
    }


    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_form = array(
            'tinymce' => true,
            'legend' => array(
                'title' => $this->l('Add Elements'),
                'icon' => 'icon-tags'
            ),
            'input' => array(
                array(
                    'type' => 'text',
                    'label' => $this->l('Libellé'),
                    'name' => 'libelle',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'textarea',
                    'label' => $this->l('Description'),
                    'name' => 'description',
                    'required' => true,
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Alt'),
                    'name' => 'alt',
                    'lang' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Lien'),
                    'name' => 'link',
                    'required' => true
                ),
                array(
                    'type' => 'text',
                    'label' => $this->l('Titre Lien'),
                    'name' => 'link_title',
                    'required' => false,
                    'lang' => true
                ),
                array(
                    'type' => 'file',
                    'label' => $this->l('Image'),
                    'name' => 'icon',
                    'desc' => $this->l('Upload an image for your icon. The recommended dimensions are 270 x 430px if you are using the default theme.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Nouvelle tab'),
                    'name' => 'new_tab',
                    'required' => false,
                    'values' => array(
                            array(
                                'id' => 'new_tab_on',
                                'value' => 1,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'new_tab_off',
                                'value' => 0,
                                'label' => $this->l('Disabled')
                            )
                        ),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('Enabled'),
                    'name' => 'statuts',
                    'is_bool' => true,
                    'default' => true,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->trans('Enabled', array(), 'Admin.Global')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->trans('Disabled', array(), 'Admin.Global')
                        )
                    ),
                ),

            ),
            'submit' => array(
                'title' => $this->l('Save'),
            )
        );
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = array(
                'type' => 'shop',
                'label' => $this->trans('Shop association', array(), 'Admin.Global'),
                'name' => 'checkBoxShopAsso',
            );
        }

        return parent::renderForm();
    }

    public function ajaxProcessUpdatePositions()
    {
        $way = (int)Tools::getValue('way');
        $id = (int)Tools::getValue('id');
        $positions = Tools::getValue($this->table);


        if (is_array($positions))
            foreach ($positions as $position => $value)
            {
                $pos = explode('_', $value);

                if (isset($pos[2]) && (int)$pos[2] === $id)
                {
                        if (isset($position) && $this->updateposition($way, $position, $id))
                            echo 'ok position '.(int)$position.' for id '.(int)$pos[1].'\r\n';
                        else
                            echo '{"hasError" : true, "errors" : "Can not update id '.(int)$id.' to position '.(int)$position.' "}';

                    break;
                }
            }

    }

    public function saveImage($item)
    {
        $exts = array('jpg', 'jpeg', 'png');

        if(!isset($_FILES[$item]) || empty($_FILES[$item]['tmp_name'])){
            return false;
        }

        // get the name of the image
        $name = str_replace(strrchr($_FILES[$item]['name'], '.'), '', $_FILES[$item]['name']);

        if (ImageManager::isCorrectImageFileExt($_FILES[$item]['name'], $exts)) {
            $names = explode('.', $_FILES[$item]['name']);
            $ext = $names[count($names) - 1];
            $image_name = $name .'-'.rand(0, 1000).'.'.$ext;
            $tmp_name = tempnam(_PS_TMP_IMG_DIR_, 'PS');

            if (!$tmp_name || !move_uploaded_file($_FILES[$item]['tmp_name'], $tmp_name)) {
                return false;
            }

            $dest_file = _PS_MODULE_DIR_ . $this->module->name.'/views/img/'.$image_name;
            if (!ImageManager::resize($tmp_name, $dest_file, null, null, $ext)) {
                return false;
            }

            if (isset($tmp_name)) {
                @unlink($tmp_name);
            }

            return $image_name;
        }
    }

    public function updateposition($way, $position, $id)
    {

        if (!$res = Db::getInstance()->executeS('
            SELECT `id_reassurance_elements`, `position`
            FROM `'._DB_PREFIX_.'reassurance_elements`
            ORDER BY `position` ASC'
        ))
            return false;

        foreach ($res as $reassurense)
            if ((int)$reassurense['id_reassurance_elements'] == (int)$id)
                $movedReassurense = $reassurense;

        if (!isset($movedReassurense) || !isset($position))
            return false;

        return (Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'reassurance_elements`
            SET `position`= `position` '.($way ? '- 1' : '+ 1').'
            WHERE `position`
            '.($way
                ? '> '.(int)$movedReassurense['position'].' AND `position` <= '.(int)$position
                : '< '.(int)$movedReassurense['position'].' AND `position` >= '.(int)$position.'
            '))
        && Db::getInstance()->execute('
            UPDATE `'._DB_PREFIX_.'reassurance_elements`
            SET `position` = '.(int)$position.'
            WHERE `id_reassurance_elements` = '.(int)$movedReassurense['id_reassurance_elements']));
    }
}