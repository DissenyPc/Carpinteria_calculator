<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'carpinteriacalculator/classes/CarpinteriaBar.php';

class CarpinteriaCalculator extends Module
{
    public function __construct()
    {
        $this->name = 'carpinteriacalculator';
        $this->tab = 'front_office_features';$this->version = '1.3.0';
        $this->author = 'Jaime Sánchez Chirivella (DissenyPc)';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName =$this->l('Calculadora de Carpintería Metálica');
        $this->description =$this->l('Cálculo dinámico de despiece de barras, importación Excel y precios por acabados W x H.');
        $this->ps_versions_compliancy = array('min' => '8.0.0', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install() 
            && $this->createTables()
            && $this->installTab()
            && $this->registerHook('displayAdminProductsExtra')
            && $this->registerHook('displayProductButtons')
            && $this->registerHook('actionProductSave');
    }

    public function uninstall()
    {
        return parent::uninstall() 
            && $this->uninstallTab()
            && $this->deleteTables();
    }

    private function installTab()
    {
        // 1. Pestaña de Catálogo e Importador de Barras
        $tab = new Tab();
        $tab->class_name = 'AdminCarpinteriaBar';
        $tab->module = $this->name;
        $tab->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tab->active = 1;
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Catálogo e Importador de Barras';
        }
        $tab->add();

        // 2. Pestaña de la Calculadora de Presupuestos en Back Office
        $tabCalc = new Tab();
        $tabCalc->class_name = 'AdminCarpinteriaCalculator';
        $tabCalc->module = $this->name;
        $tabCalc->id_parent = (int) Tab::getIdFromClassName('AdminCatalog');
        $tabCalc->active = 1;
        foreach (Language::getLanguages(true) as $lang) {
            $tabCalc->name[$lang['id_lang']] = 'Calculadora de Presupuestos';
        }
        return $tabCalc->add();
    }

    private function uninstallTab()
    {
        $classes = array('AdminCarpinteriaBar', 'AdminCarpinteriaCalculator');
        foreach ($classes as $className) {
            $id_tab = (int) Tab::getIdFromClassName($className);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                $tab->delete();
            }
        }
        return true;
    }

    private function createTables()
    {
        $sqls = array();

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_bar` (
            `id_bar` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `reference` VARCHAR(64) DEFAULT NULL,
            `name` VARCHAR(150) NOT NULL,
            `serie` VARCHAR(64) DEFAULT NULL,
            `price_plata` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_l_blanco` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_col_std` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_l_color` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bronce` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_inox` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_madera` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_ezzy` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_metal` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_anod_negro` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol01` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol08` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol09` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY (`id_bar`),
            KEY `reference` (`reference`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_hueco` (
            `id_hueco` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `reference` VARCHAR(64) NOT NULL,
            `name` VARCHAR(150) NOT NULL,
            `serie` VARCHAR(64) DEFAULT NULL,
            `price_plata` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_l_blanco` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_col_std` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_l_color` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bronce` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_inox` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_madera` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_ezzy` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_metal` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_anod_negro` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol01` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol08` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            `price_bicol09` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
            PRIMARY KEY (`id_hueco`),
            UNIQUE KEY `reference` (`reference`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_colors` (
            `id_product_color` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `color_name` VARCHAR(100) NOT NULL,
            `color_field_key` VARCHAR(50) NOT NULL DEFAULT "price_l_blanco",
            `is_default` TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id_product_color`),
            KEY `id_product` (`id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_config` (
            `id_product` INT(11) UNSIGNED NOT NULL,
            `min_width` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
            `max_width` DECIMAL(10,2) NOT NULL DEFAULT 3000.00,
            `min_height` DECIMAL(10,2) NOT NULL DEFAULT 500.00,
            `max_height` DECIMAL(10,2) NOT NULL DEFAULT 3000.00,
            `assembly_labor_cost` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (`id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_recipe` (
            `id_recipe` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `id_bar` INT(11) UNSIGNED NOT NULL,
            `part_label` VARCHAR(100) NOT NULL,
            `quantity` INT(11) UNSIGNED NOT NULL DEFAULT 1,
            `formula_len` VARCHAR(255) NOT NULL,
            `cut_angle` VARCHAR(20) DEFAULT "90-90",
            PRIMARY KEY (`id_recipe`),
            KEY `id_product` (`id_product`),
            KEY `id_bar` (`id_bar`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote` (
            `id_quote` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_employee` INT(11) UNSIGNED NOT NULL,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_quote`),
            KEY `id_employee` (`id_employee`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        $sqls[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote_line` (
            `id_quote_line` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_quote` INT(11) UNSIGNED NOT NULL,
            `id_product` INT(11) UNSIGNED NOT NULL,
            `width` DECIMAL(10,2) NOT NULL,
            `height` DECIMAL(10,2) NOT NULL,
            `color_field_key` VARCHAR(32) NOT NULL,
            `add_overlap` TINYINT(1) NOT NULL DEFAULT 0,
            `overlap_id_bar` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `id_hueco` INT(11) UNSIGNED NOT NULL DEFAULT 0,
            `holes` TEXT NOT NULL,
            `add_blind` TINYINT(1) NOT NULL DEFAULT 0,
            `materials_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `labor_cost` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `final_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_quote_line`),
            KEY `id_quote` (`id_quote`),
            KEY `id_product` (`id_product`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        foreach ($sqls as$sql) {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        }

        return true;
    }

    private function deleteTables()
    {
        $sqls = array(
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote_line`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_bar`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_hueco`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_colors`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_config`',
            'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'carpinteria_product_recipe`',
        );

        foreach ($sqls as$sql) {
            if (!Db::getInstance()->execute($sql)) {
                return false;
            }
        }

        return true;
    }

    public function hookDisplayProductButtons($params)
    {
        $product = isset($params['product']) ? $params['product'] : [];
        $id_product = (int) (isset($product['id_product']) ? $product['id_product'] : 0);

        if (!$id_product) {
            return '';
        }

        $config = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_product_config` WHERE `id_product` = ' . $id_product
        );

        if (!$config) {
            return '';
        }

        $this->context->smarty->assign([
            'id_product' => $id_product,
            'config' => $config,
            'is_employee' => $this->isBackOfficeEmployeeLogged(),
            'ajax_url' => $this->context->link->getModuleLink('carpinteriacalculator', 'calculate'),
        ]);

        return $this->display(__FILE__, 'views/templates/hooks/calculator.tpl');
    }

    public function isBackOfficeEmployeeLogged()
    {
        $backOfficeCookie = new Cookie('psAdmin');
        $id_employee = (int) $backOfficeCookie->id_employee;

        if (!$id_employee || !$backOfficeCookie->isSessionAlive()) {
            return false;
        }

        $employee = new Employee($id_employee);

        return Validate::isLoadedObject($employee)
            && (bool) $employee->active
            && Employee::checkPassword($id_employee, $backOfficeCookie->passwd)
            && (
                !isset($backOfficeCookie->remote_addr)
                || $backOfficeCookie->remote_addr == ip2long(Tools::getRemoteAddr())
                || !Configuration::get('PS_COOKIE_CHECKIP')
            );
    }

    public function getLoggedBackOfficeEmployeeId()
    {
        if (!$this->isBackOfficeEmployeeLogged()) {
            return 0;
        }

        $backOfficeCookie = new Cookie('psAdmin');

        return (int) $backOfficeCookie->id_employee;
    }

    public function getBackOfficeToken()
    {
        $id_employee = $this->getLoggedBackOfficeEmployeeId();
        if (!$id_employee) {
            return '';
        }

        return Tools::getAdminToken(
            'AdminCarpinteriaCalculator' . (int) Tab::getIdFromClassName('AdminCarpinteriaCalculator') . $id_employee
        );
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        $id_product = (int)$params['id_product'];

        $config = Db::getInstance()->getRow('SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_product_config` WHERE `id_product` = ' . $id_product);
        if (!$config) {$config = array(
                'min_width' => 500,
                'max_width' => 3000,
                'min_height' => 500,
                'max_height' => 3000,
                'assembly_labor_cost' => 0.00
            );
        }

        $recipe = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_product_recipe` WHERE `id_product` = ' . $id_product);$bars = Db::getInstance()->executeS('SELECT id_bar, reference, name FROM `' . _DB_PREFIX_ . 'carpinteria_bar`');

        $this->context->smarty->assign(array(
            'carpinteria_config' => $config,
            'carpinteria_recipe' => $recipe,
            'carpinteria_bars' => $bars,
            'id_product' => $id_product,
        ));

        return $this->display(__FILE__, 'views/templates/admin/product_extra.tpl');
    }

    public function hookActionProductSave($params)
    {
        $id_product = (int)$params['id_product'];
        if (!$id_product) {
            return;
        }

        $min_width = (float) Tools::getValue('carpinteria_min_width', 500);
        $max_width = (float) Tools::getValue('carpinteria_max_width', 3000);$min_height = (float) Tools::getValue('carpinteria_min_height', 500);
        $max_height = (float) Tools::getValue('carpinteria_max_height', 3000);$assembly_labor_cost = (float) Tools::getValue('carpinteria_assembly_labor_cost', 0.00);

        Db::getInstance()->execute('REPLACE INTO `' . _DB_PREFIX_ . 'carpinteria_product_config` 
            (`id_product`, `min_width`, `max_width`, `min_height`, `max_height`, `assembly_labor_cost`) 
            VALUES (' . $id_product . ', ' .$min_width . ', ' . $max_width . ', ' .$min_height . ', ' . $max_height . ', ' .$assembly_labor_cost . ')');

        $parts = Tools::getValue('carpinteria_part_label', array());
        if (is_array($parts)) {
            Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'carpinteria_product_recipe` WHERE `id_product` = ' . $id_product);

            $bar_ids = Tools::getValue('carpinteria_id_bar', array());$quantities = Tools::getValue('carpinteria_quantity', array());
            $formulas = Tools::getValue('carpinteria_formula_len', array());$angles = Tools::getValue('carpinteria_cut_angle', array());

            foreach ($parts as $key =>$part_label) {
                if (empty($part_label)) {
                    continue;
                }
                $id_bar = (int) ($bar_ids[$key] ?? 0);$quantity = (int) ($quantities[$key] ?? 1);
                $formula_len = pSQL($formulas[$key] ?? 'W');$cut_angle = pSQL($angles[$key] ?? '90-90');

                if ($id_bar > 0) {
                    Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'carpinteria_product_recipe` 
                        (`id_product`, `id_bar`, `part_label`, `quantity`, `formula_len`, `cut_angle`) 
                        VALUES (' . $id_product . ', ' .$id_bar . ', \'' . pSQL($part_label) . '\', ' .$quantity . ', \'' . $formula_len . '\', \'' .$cut_angle . '\')');
                }
            }
        }
    }
}