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
        $quotes = Db::getInstance()->executeS('
            SELECT q.`id_quote`, q.`date_add`, q.`date_upd`,
                COUNT(l.`id_quote_line`) AS `line_count`,
                COALESCE(SUM(l.`final_price`), 0) AS `total`
            FROM `' . _DB_PREFIX_ . 'carpinteria_quote` q
            LEFT JOIN `' . _DB_PREFIX_ . 'carpinteria_quote_line` l ON (l.`id_quote` = q.`id_quote`)
            GROUP BY q.`id_quote`
            ORDER BY q.`date_upd` DESC
        ');
        $selectedQuoteId = (int) Tools::getValue('id_quote');
        $selectedQuote = null;
        $quoteLines = [];
        if ($selectedQuoteId) {
            $selectedQuote = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_quote` WHERE `id_quote` = ' . $selectedQuoteId
            );
            if ($selectedQuote) {
                $quoteLines = Db::getInstance()->executeS('
                    SELECT l.*, pl.`name` AS `product_name`
                    FROM `' . _DB_PREFIX_ . 'carpinteria_quote_line` l
                    LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                        ON (pl.`id_product` = l.`id_product`
                            AND pl.`id_lang` = ' . (int) $this->context->language->id . '
                            AND pl.`id_shop` = ' . (int) $this->context->shop->id . ')
                    WHERE l.`id_quote` = ' . $selectedQuoteId . '
                    ORDER BY l.`id_quote_line` ASC
                ');
            }
        }

        $this->context->smarty->assign([
            'carpinteria_products' => $products,
            'carpinteria_overlap_bars' => $overlapBars,
            'carpinteria_huecos' => $huecos,
            'carpinteria_quotes' => $quotes,
            'selected_quote' => $selectedQuote,
            'selected_quote_id' => $selectedQuote ? $selectedQuoteId : 0,
            'carpinteria_quote_lines' => $quoteLines,
            'ajax_url' => $this->context->link->getModuleLink('carpinteriacalculator', 'calculate'),
            'quote_token' => $this->module->getBackOfficeToken(),
            'admin_url' => $this->context->link->getAdminLink('AdminCarpinteriaCalculator'),
        ]);

        $this->setTemplate('calculator_back.tpl');
    }
}