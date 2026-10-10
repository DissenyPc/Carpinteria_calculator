<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_2_0($module)
{
    return Db::getInstance()->execute(
        'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'carpinteria_hueco` (
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
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4'
    );
}
