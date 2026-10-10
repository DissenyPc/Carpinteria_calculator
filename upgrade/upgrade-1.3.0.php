<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_3_0($module)
{
    $sql = [];
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote` (
        `id_quote` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
        `id_employee` INT(11) UNSIGNED NOT NULL,
        `date_add` DATETIME NOT NULL,
        `date_upd` DATETIME NOT NULL,
        PRIMARY KEY (`id_quote`),
        KEY `id_employee` (`id_employee`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';
    $sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_quote_line` (
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
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            return false;
        }
    }

    return true;
}
