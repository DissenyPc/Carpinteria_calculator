<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CarpinteriaBar extends ObjectModel
{
    public $id_bar;
    public $reference;
    public $name;
    public $serie;
    public $price_plata;
    public $price_l_blanco;
    public $price_col_std;
    public $price_l_color;
    public $price_bronce;
    public $price_inox;
    public $price_madera;
    public $price_ezzy;
    public $price_metal;
    public $price_anod_negro;
    public $price_bicol01;
    public $price_bicol08;
    public $price_bicol09;

    public static $definition = array(
        'table' => 'carpinteria_bar',
        'primary' => 'id_bar',
        'fields' => array(
            'reference' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 150),
            'serie' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64),
            'price_plata' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_l_blanco' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_col_std' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_l_color' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_bronce' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_inox' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_madera' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_ezzy' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_metal' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_anod_negro' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_bicol01' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_bicol08' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
            'price_bicol09' => array('type' => self::TYPE_FLOAT, 'validate' => 'isPrice'),
        ),
    );
}