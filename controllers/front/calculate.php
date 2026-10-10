<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CarpinteriaCalculatorCalculateModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // 1. Restricción de acceso: solo empleados con sesión válida en el Back Office
        if (!$this->isLoggedEmployee()) {
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'message' => 'Acceso restringido. Inicie sesión en el Back Office para utilizar la calculadora.'
            ]));
        }

        // 2. Obtener parámetros enviados por AJAX
        $id_product = (int) Tools::getValue('id_product');
        $width = (float) Tools::getValue('width');
        $height = (float) Tools::getValue('height');
        $color_field_key = Tools::getValue('color_field_key', 'price_l_blanco');
        $validPriceFields = [
            'price_plata',
            'price_l_blanco',
            'price_col_std',
            'price_l_color',
            'price_bronce',
            'price_inox',
            'price_madera',
            'price_ezzy',
            'price_metal',
            'price_anod_negro',
            'price_bicol01',
            'price_bicol08',
            'price_bicol09',
        ];

        if (!in_array($color_field_key, $validPriceFields, true)) {
            $color_field_key = 'price_l_blanco';
        }

        if (!$id_product || $width <= 0 || $height <= 0) {
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'message' => 'Medidas no válidas.'
            ]));
        }

        // 3. Validar límites de dimensiones del producto
        $config = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_product_config` WHERE `id_product` = ' . (int) $id_product
        );

        if ($config) {
            if ($width < (float) $config['min_width'] || $width > (float) $config['max_width'] || $height < (float) $config['min_height'] || $height > (float) $config['max_height']) {
                header('Content-Type: application/json');
                die(json_encode([
                    'success' => false,
                    'message' => sprintf(
                        'Las medidas deben estar entre %d x %d mm y %d x %d mm.',
                        (int) $config['min_width'],
                        (int) $config['min_height'],
                        (int) $config['max_width'],
                        (int) $config['max_height']
                    )
                ]));
            }
        }

        $holesJson = Tools::getValue('holes', '[]');
        $holes = is_string($holesJson) ? json_decode($holesJson, true) : null;
        if (!is_array($holes) || json_last_error() !== JSON_ERROR_NONE) {
            header('Content-Type: application/json');
            die(json_encode(['success' => false, 'message' => 'La lista de huecos no tiene un formato válido.']));
        }

        $normalizedHoles = [];
        foreach ($holes as $hole) {
            if (!is_array($hole)) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Las medidas de los huecos no tienen un formato válido.']));
            }
            $holeWidth = isset($hole['width']) ? (float) $hole['width'] : 0;
            $holeHeight = isset($hole['height']) ? (float) $hole['height'] : 0;
            if ($holeWidth <= 0 || $holeHeight <= 0 || $holeWidth > $width || $holeHeight > $height) {
                header('Content-Type: application/json');
                die(json_encode([
                    'success' => false,
                    'message' => 'Cada hueco debe tener medidas positivas y no superar las medidas del producto.',
                ]));
            }
            $normalizedHoles[] = ['width' => $holeWidth, 'height' => $holeHeight];
        }

        $id_hueco = (int) Tools::getValue('id_hueco');
        $hueco = null;
        if ($normalizedHoles) {
            if (!$id_hueco) {
                header('Content-Type: application/json');
                die(json_encode([
                    'success' => false,
                    'message' => 'Selecciona un material para calcular el coste de los huecos.',
                ]));
            }
            $hueco = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_hueco` WHERE `id_hueco` = ' . $id_hueco
            );
            if (!$hueco) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'El material de hueco seleccionado no existe.']));
            }
        }

        $addOverlap = (int) Tools::getValue('add_overlap') === 1;
        $overlapId = (int) Tools::getValue('overlap_id_bar');
        $overlapBar = null;
        if ($addOverlap) {
            $overlapBar = Db::getInstance()->getRow(
                'SELECT `id_bar`, `reference`, `name`, `serie`, `' . bqSQL($color_field_key) . '` AS `bar_price`
                FROM `' . _DB_PREFIX_ . 'carpinteria_bar`
                WHERE `id_bar` = ' . $overlapId . ' AND LOWER(`name`) LIKE \'%solape%\''
            );
            if (!$overlapBar) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Selecciona un perfil válido que contenga “solape”.']));
            }
        }

        $addBlind = (int) Tools::getValue('add_blind') === 1;

        // 4. Obtener la receta de despiece del producto
        $recipe = Db::getInstance()->executeS(
            'SELECT r.*, b.name as bar_name, b.reference as bar_ref, b.`' . $color_field_key . '` as bar_price
            FROM `' . _DB_PREFIX_ . 'carpinteria_product_recipe` r
            LEFT JOIN `' . _DB_PREFIX_ . 'carpinteria_bar` b ON (r.id_bar = b.id_bar)
            WHERE r.id_product = ' . (int) $id_product
        );

        $breakdown = [];
        $total_materials_cost = 0.0;

        foreach ($recipe as $item) {
            // Evaluador seguro de fórmulas basadas en W y H
            $formula = str_replace(['W', 'H', 'w', 'h'], [$width, $height, $width, $height], $item['formula_len']);

            // Sanitización estricta de la fórmula aritmética
            $formula_clean = preg_replace('/[^0-9\+\-\*\/\(\)\.\s]/', '', $formula);
            $calculated_len_mm = 0.0;

            if (!empty($formula_clean) && preg_match('/^[0-9\s\+\-\*\/\(\)\.]+$/', $formula_clean)) {
                try {
                    @eval('$calculated_len_mm = (' . $formula_clean . ');');
                } catch (Throwable $e) {
                    $calculated_len_mm = 0.0;
                }
            }

            $length_meters = $calculated_len_mm / 1000;
            $price_per_meter = (float) $item['bar_price'];
            $line_cost = $length_meters * $price_per_meter * (int) $item['quantity'];

            $total_materials_cost += $line_cost;

            $breakdown[] = [
                'part_label' => $item['part_label'],
                'quantity' => (int) $item['quantity'],
                'length_mm' => round($calculated_len_mm, 2),
                'cut_angle' => $item['cut_angle'],
                'bar_ref' => $item['bar_ref'],
                'bar_name' => $item['bar_name'],
                'line_cost' => round($line_cost, 2),
            ];
        }

        if ($addOverlap) {
            $overlapLengthMm = 2 * ($width + $height);
            $overlapLineCost = ($overlapLengthMm / 1000) * (float) $overlapBar['bar_price'];
            $total_materials_cost += $overlapLineCost;
            $breakdown[] = [
                'part_label' => 'Solape',
                'quantity' => 1,
                'length_mm' => round($overlapLengthMm, 2),
                'cut_angle' => '',
                'bar_ref' => $overlapBar['reference'],
                'bar_name' => $overlapBar['name'],
                'line_cost' => round($overlapLineCost, 2),
            ];
        }

        if ($normalizedHoles) {
            foreach ($normalizedHoles as $index => $holeDimensions) {
                $areaM2 = ($holeDimensions['width'] * $holeDimensions['height']) / 1000000;
                $holeLineCost = $areaM2 * (float) $hueco[$color_field_key];
                $total_materials_cost += $holeLineCost;
                $breakdown[] = [
                    'part_label' => 'Hueco ' . ($index + 1),
                    'quantity' => 1,
                    'length_mm' => 0,
                    'area_m2' => round($areaM2, 4),
                    'cut_angle' => '',
                    'bar_ref' => $hueco['reference'],
                    'bar_name' => $hueco['name'],
                    'line_cost' => round($holeLineCost, 2),
                ];
            }
        }

        if ($addBlind) {
            $blindAreaM2 = ($width * $height) / 1000000;
            $blindLineCost = $blindAreaM2 * 50;
            $total_materials_cost += $blindLineCost;
            $breakdown[] = [
                'part_label' => 'Persiana',
                'quantity' => 1,
                'length_mm' => 0,
                'area_m2' => round($blindAreaM2, 4),
                'cut_angle' => '',
                'bar_ref' => '',
                'bar_name' => '50 €/m²',
                'line_cost' => round($blindLineCost, 2),
            ];
        }

        $labor_cost = isset($config['assembly_labor_cost']) ? (float) $config['assembly_labor_cost'] : 0.00;
        $final_price = $total_materials_cost + $labor_cost;

        header('Content-Type: application/json');
        die(json_encode([
            'success' => true,
            'final_price' => round($final_price, 2),
            'materials_cost' => round($total_materials_cost, 2),
            'labor_cost' => round($labor_cost, 2),
            'breakdown' => $breakdown,
        ]));
    }

    private function isLoggedEmployee()
    {
        return $this->module->isBackOfficeEmployeeLogged();
    }
}