<?php

class Reassurances extends ObjectModel {

    /** @var string */
    public $libelle;

    /** @var string */
    public $description;

    /** @var string */
    public $icon;

    /** @var string */
    public $alt;

    /** @var bool */
    public $statuts = true;

    /** @var int */
    public $position;

    /** @var string */
    public $link;

    /** @var string */
    public $link_title;

    /** @var bool */
    public $new_tab;

    // /** @var date */
    // public $created_at;

    // /** @var date */
    // public $updated_at;

    /**
     * Example from the CMS model (CMSCore)
     */
    public static $definition = [
        'table' => 'reassurance_elements',
        'primary' => 'id_reassurance_elements',
        'multilang' => true,
        // 'multishop' => true,
        'multilang_shop' => true,
        'fields' => array(
            'libelle'   => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 60, 'required' => true],
            'description'  => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true],
            'icon'      => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'alt'       => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 100],
            'statuts'    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'size' => 1, 'required' => true],
            'position'     => ['type' => self::TYPE_STRING, 'validate' => 'isInt', 'size' => 11, 'required' => true],
            'link'      => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 250, 'required' => true],
            'link_title'    => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 100],
            'new_tab'    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'size' => 1],
            // 'created_at'    => ['type' => self::TYPE_DATE , 'validate' => 'isDate'],
            // 'updated_at'    => ['type' => self::TYPE_BOOL, 'validate' => 'isDate']
        )
    ];


    public static function getReassurance(){
        $id_shop = Context::getContext()->shop->id;
        $id_lang = Context::getContext()->language->id;

        $sql = "SELECT `r`.*, `rl`.*  FROM `"._DB_PREFIX_."reassurance_elements` AS `r`
                INNER JOIN `"._DB_PREFIX_."reassurance_elements_lang` AS `rl` ON `r`.`id_reassurance_elements` = `rl`.`id_reassurance_elements`
                INNER JOIN `"._DB_PREFIX_."reassurance_elements_shop` AS `rs` ON `r`.`id_reassurance_elements` = `rs`.`id_reassurance_elements`
                WHERE `rl`.`id_lang` = $id_lang AND `rs`.`id_shop` = $id_shop AND `r`.`statuts` = 1
                ORDER BY `r`.`position` ASC";

        return DB::getInstance()->executeS($sql);
    }

    public static function getReassurancesCount(){
        $sql = "SELECT COUNT(`id_reassurance_elements`) AS `count` FROM `"._DB_PREFIX_."reassurance_elements`";

        $res = DB::getInstance()->getRow($sql);

        return isset($res) ? $res['count'] : 0;
    }
}