<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCarpinteriaCalculatorController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        // Obtener la lista de productos configurados para la carpintería[cite: 1]
        $products = Db::getInstance()->executeS('
            SELECT p.id_product, pl.name 
            FROM `' . _DB_PREFIX_ . 'carpinteria_product_config` c
            LEFT JOIN `' . _DB_PREFIX_ . 'product` p ON (c.id_product = p.id_product)
            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.id_product = pl.id_product AND pl.id_lang = ' . (int)$this->context->language->id . ')
        ');
        $overlapBars = Db::getInstance()->executeS(
            'SELECT `id_bar`, `reference`, `name`, `serie` FROM `' . _DB_PREFIX_ . 'carpinteria_bar`
            WHERE LOWER(`name`) LIKE \'%solape%\' ORDER BY `name`'
        );
        $huecos = Db::getInstance()->executeS(
            'SELECT `id_hueco`, `reference`, `name` FROM `' . _DB_PREFIX_ . 'carpinteria_hueco` ORDER BY `name`'
        );

        $this->context->smarty->assign([
            'carpinteria_products' => $products,
            'carpinteria_overlap_bars' => $overlapBars,
            'carpinteria_huecos' => $huecos,
            'ajax_url' => $this->context->link->getModuleLink('carpinteriacalculator', 'calculate'),
        ]);

        $this->setTemplate('calculator_back.tpl');
    }
}