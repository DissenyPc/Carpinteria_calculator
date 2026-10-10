<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCarpinteriaBarController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        parent::__construct();
    }

    public function initContent()
    {
        parent::initContent();

        $message = '';
        $error = false;

        if (Tools::isSubmit('submitImportCsv')) {
            if (!isset($_FILES['carpinteria_csv_file']) || $_FILES['carpinteria_csv_file']['error'] !== UPLOAD_ERR_OK) {
                $error = true;
                $message = 'Selecciona un archivo CSV de barras válido.';
            } else {
                $result = $this->importCatalogCsv($_FILES['carpinteria_csv_file']['tmp_name'], 'carpinteria_bar', 'id_bar');
                $error = !$result['success'];
                $message = $result['message'];
            }
        } elseif (Tools::isSubmit('submitUploadHuecosCsv')) {
            if (!isset($_FILES['huecos_csv_file']) || $_FILES['huecos_csv_file']['error'] !== UPLOAD_ERR_OK) {
                $error = true;
                $message = 'Selecciona un archivo CSV de huecos válido.';
            } else {
                $result = $this->importCatalogCsv($_FILES['huecos_csv_file']['tmp_name'], 'carpinteria_hueco', 'id_hueco');
                $error = !$result['success'];
                $message = $result['message'];
            }
        }

        // Obtener el listado actual de barras para mostrarlas en una tabla resumen
        $bars = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_bar` ORDER BY `id_bar` DESC');
        $huecos = Db::getInstance()->executeS('SELECT * FROM `' . _DB_PREFIX_ . 'carpinteria_hueco` ORDER BY `id_hueco` DESC');

        $this->context->smarty->assign([
            'confirmation_message' => $message,
            'error_message' => $error,
            'bars_list' => $bars,
            'huecos_list' => $huecos,
            'import_url' => $_SERVER['REQUEST_URI'],
        ]);

        $this->content .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'carpinteriacalculator/views/templates/admin/import_bar.tpl'
        );
        $this->context->smarty->assign('content', $this->content);
    }

    private function importCatalogCsv($filePath, $tableName, $idColumn)
    {
        $allowedTables = [
            'carpinteria_bar' => 'id_bar',
            'carpinteria_hueco' => 'id_hueco',
        ];
        if (!isset($allowedTables[$tableName]) || $allowedTables[$tableName] !== $idColumn) {
            return ['success' => false, 'message' => 'Destino de importación no válido.'];
        }

        if (!is_file($filePath) || !is_readable($filePath)) {
            return ['success' => false, 'message' => 'No se encuentra o no se puede leer el CSV seleccionado.'];
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            return ['success' => false, 'message' => 'No se pudo abrir el archivo CSV.'];
        }

        $headers = fgetcsv($handle, 0, ';');
        if ($headers === false) {
            fclose($handle);
            return ['success' => false, 'message' => 'El archivo CSV está vacío o no tiene una cabecera válida.'];
        }

        $aliases = [
            'reference' => ['reference', 'referencia', 'codigo'],
            'name' => ['name', 'nombre', 'descripcion'],
            'serie' => ['serie'],
            'price_plata' => ['priceplata', 'plata'],
            'price_l_blanco' => ['pricelblanco', 'lblanco'],
            'price_col_std' => ['pricecolstd', 'colstd'],
            'price_l_color' => ['pricelcolor', 'lcolor'],
            'price_bronce' => ['pricebronce', 'bronce'],
            'price_inox' => ['priceinox', 'inox'],
            'price_madera' => ['pricemadera', 'madera'],
            'price_ezzy' => ['priceezzy', 'ezzy'],
            'price_metal' => ['pricemetal', 'metal'],
            'price_anod_negro' => ['priceanodnegro', 'anodnegro'],
            'price_bicol01' => ['pricebicol01', 'bicol01'],
            'price_bicol08' => ['pricebicol08', 'bicol08'],
            'price_bicol09' => ['pricebicol09', 'bicol09'],
        ];

        $headerIndexes = [];
        foreach ($headers as $index => $header) {
            $normalized = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', trim((string) $header, "\xEF\xBB\xBF \t\n\r\0\x0B")));
            foreach ($aliases as $field => $fieldAliases) {
                if (in_array($normalized, $fieldAliases, true)) {
                    $headerIndexes[$field] = $index;
                    break;
                }
            }
        }

        $missingFields = array_diff(array_keys($aliases), array_keys($headerIndexes));
        if ($missingFields) {
            fclose($handle);
            return [
                'success' => false,
                'message' => 'Faltan columnas requeridas en el CSV: ' . implode(', ', $missingFields) . '.',
            ];
        }

        $priceFields = array_slice(array_keys($aliases), 3);
        $importedCount = 0;
        $rowNumber = 1;
        $db = Db::getInstance();
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            ++$rowNumber;
            $referenceValue = trim((string) ($row[$headerIndexes['reference']] ?? ''));
            if ($referenceValue === '') {
                continue;
            }

            $values = [
                'reference' => pSQL($referenceValue),
                'name' => pSQL(trim((string) ($row[$headerIndexes['name']] ?? ''))),
                'serie' => isset($headerIndexes['serie']) ? pSQL(trim((string) ($row[$headerIndexes['serie']] ?? ''))) : '',
            ];

            foreach ($priceFields as $field) {
                $rawPrice = trim((string) (isset($headerIndexes[$field]) ? ($row[$headerIndexes[$field]] ?? '0') : '0'));
                $rawPrice = str_replace(',', '.', $rawPrice);
                if ($rawPrice === '') {
                    $rawPrice = '0';
                }
                if (!is_numeric($rawPrice)) {
                    fclose($handle);
                    return ['success' => false, 'message' => 'Precio no numérico en la fila ' . $rowNumber . ' (' . $field . ').'];
                }
                $values[$field] = (float) $rawPrice;
            }

            $existingId = (int) $db->getValue(
                'SELECT `' . bqSQL($idColumn) . '` FROM `' . _DB_PREFIX_ . bqSQL($tableName) .
                '` WHERE `reference` = \'' . pSQL($referenceValue) . '\''
            );
            $updates = [];
            foreach ($values as $field => $value) {
                if ($field === 'reference') {
                    continue;
                }
                $sqlValue = in_array($field, $priceFields, true)
                    ? (string) (float) $value
                    : '\'' . pSQL($value) . '\'';
                $updates[] = '`' . bqSQL($field) . '` = ' . $sqlValue;
            }

            if ($existingId) {
                $success = $db->execute(
                    'UPDATE `' . _DB_PREFIX_ . bqSQL($tableName) . '` SET ' . implode(', ', $updates) .
                    ' WHERE `' . bqSQL($idColumn) . '` = ' . $existingId
                );
            } else {
                $columns = array_map(function ($field) {
                    return '`' . bqSQL($field) . '`';
                }, array_keys($values));
                $sqlValues = [];
                foreach ($values as $field => $value) {
                    $sqlValues[] = in_array($field, $priceFields, true)
                        ? (string) (float) $value
                        : '\'' . pSQL($value) . '\'';
                }
                $success = $db->execute(
                    'INSERT INTO `' . _DB_PREFIX_ . bqSQL($tableName) . '` (' . implode(', ', $columns) .
                    ') VALUES (' . implode(', ', $sqlValues) . ')'
                );
            }

            if (!$success) {
                fclose($handle);
                return ['success' => false, 'message' => 'Error de base de datos en la fila ' . $rowNumber . ': ' . $db->getMsgError()];
            }
            ++$importedCount;
        }

        fclose($handle);
        return [
            'success' => true,
            'message' => 'Importación completada: ' . $importedCount . ' registros insertados o actualizados.',
        ];
    }
}