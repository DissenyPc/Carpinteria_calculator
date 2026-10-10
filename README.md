# Calculadora de Carpintería Metálica

Módulo de PrestaShop para configurar productos de carpintería metálica, mantener catálogos de perfiles y materiales, y calcular presupuestos y despieces a partir de las medidas de ancho (`W`) y alto (`H`).

## Características

- Configuración de medidas mínimas y máximas por producto.
- Recetas de despiece con barras, cantidad, fórmula de longitud y ángulo de corte.
- Importación de perfiles y precios por acabado.
- Importación de materiales de hueco, como cristal, madera o metacrilato.
- Cálculo opcional de un perfil con “solape” para el perímetro.
- Cálculo opcional de persiana a **50 €/m²**.
- Calculadora interna accesible desde el Back Office.
- Calculadora en la ficha de producto del Front Office para empleados con sesión válida en el Back Office.

## Requisitos

- PrestaShop 8.0 o posterior.
- PHP y base de datos compatibles con la versión de PrestaShop instalada.
- Permisos del Back Office para gestionar módulos, productos y el catálogo del módulo.

## Instalación

1. Copia la carpeta `carpinteriacalculator` en `modules/` o comprime la carpeta y súbela desde **Módulos → Gestor de módulos → Subir un módulo**.
2. Instala **Calculadora de Carpintería Metálica** desde el Gestor de módulos.
3. La instalación crea las tablas del catálogo, huecos, configuración de producto y recetas, y añade las páginas de administración del módulo.

> **Importante:** desinstalar el módulo elimina sus tablas de datos. Haz una copia de seguridad antes de desinstalarlo.

## Actualización

Al actualizar una instalación existente, utiliza **Actualizar** en el Gestor de módulos y conserva la instalación. La migración `upgrade/upgrade-1.2.0.php` crea la tabla de materiales de hueco.

No desinstales el módulo para actualizarlo: el proceso de desinstalación elimina las tablas y los datos guardados.

## Configuración de productos

En la edición de un producto, utiliza la pestaña de configuración de carpintería para establecer:

- Ancho y alto mínimos y máximos, en milímetros.
- Coste fijo de mano de obra.
- Componentes de la receta:
  - Etiqueta del componente.
  - Barra del catálogo.
  - Cantidad.
  - Fórmula de longitud.
  - Ángulo de corte.

Las fórmulas pueden usar `W` para el ancho y `H` para el alto. Por ejemplo:

```text
H
W - 45
(W / 2) + 12
```

## Catálogos e importación CSV

Abre **Catálogo e Importador de Barras** en el Back Office.

### Barras y perfiles Alugom

- El formulario permite importar un CSV de barras.
- Para incorporar perfiles con “solape”, importa el CSV de barras de tu proveedor usando el formulario de archivo.
- El separador esperado es punto y coma (`;`).

### Materiales para huecos

- Importa el catálogo de materiales con el formulario de archivo.
- Se espera que los precios de los materiales sean precios por metro cuadrado (**€/m²**).

Los encabezados se identifican por nombre, no por una posición fija. Los datos deben incluir referencia/código, nombre/descripción, serie y columnas de precios por acabado. Se reconocen los formatos de encabezado de `Alugom.csv` y `huecos.csv`.

Las importaciones insertan referencias nuevas y actualizan las existentes. Los CSV deben mantenerse fuera de este repositorio público si contienen precios comerciales.

## Uso del calculador

En **Calculadora de Presupuestos**:

1. Selecciona un producto configurado.
2. Introduce el ancho y el alto en milímetros.
3. Selecciona el acabado.
4. Si corresponde, marca **Añadir solape al perímetro** y selecciona un perfil con “solape”.
5. Selecciona un material para los huecos y añade uno o más pares de medidas de ancho y alto.
6. Marca **Añadir persiana** si debe incluirse.
7. Pulsa **Calcular Presupuesto y Despiece**.

El calculador valida las medidas contra los límites configurados en el producto. Cada hueco debe tener medidas positivas y no superar las medidas del producto.

### Cálculos adicionales

- **Solape:** coste del perfil seleccionado para el perímetro completo:

  ```text
  perímetro en metros × precio del perfil por metro
  ```

- **Huecos:** para cada hueco:

  ```text
  (ancho en mm × alto en mm / 1.000.000) × precio del material por m²
  ```

- **Persiana:**

  ```text
  (ancho en mm × alto en mm / 1.000.000) × 50 €
  ```

Los costes de barras, huecos y persiana se agregan al coste de materiales. La mano de obra configurada se agrega al precio final.

## Acceso y hooks

- El controlador AJAX de cálculo comprueba que haya una sesión válida de empleado del Back Office.
- El módulo usa `displayProductButtons` para insertar la calculadora en la ficha de producto. Si el módulo ya estaba instalado y el botón no aparece, comprueba que esté asociado a ese hook en **Diseño → Posiciones**.
- Para que el calculador del Front Office se muestre, el producto debe tener una configuración guardada y el tema debe ejecutar el hook `displayProductButtons`.

## Estructura del módulo

```text
carpinteriacalculator/
├── classes/
├── controllers/
│   ├── admin/
│   └── front/
├── upgrade/
├── views/templates/
│   ├── admin/
│   └── hooks/
└── README.md
```

Los catálogos CSV comerciales no se incluyen en este repositorio público. Solicita o prepara los CSV de tu proveedor y súbelos desde los formularios de importación del Back Office.

## Diagnóstico básico

- **No aparece la calculadora en el producto:** confirma que el producto tiene configuración guardada, que el módulo está asociado a `displayProductButtons` y que el empleado mantiene iniciada la sesión del Back Office.
- **No aparecen perfiles con solape:** importa el catálogo de barras y verifica que el nombre de los perfiles contenga “solape”.
- **No aparecen materiales:** importa el CSV de materiales de huecos desde el catálogo del módulo.
- **La importación falla:** comprueba que el CSV use `;` como separador, tenga todos los encabezados requeridos y use valores numéricos válidos en las columnas de precio.
- **No se crea la tabla de huecos tras actualizar:** confirma que la actualización del módulo se ejecutó; no lo desinstales para forzarla.

## Datos y privacidad

El módulo guarda catálogos de perfiles y huecos, límites de medidas, coste de mano de obra y recetas asociadas a productos en la base de datos de PrestaShop. Los presupuestos calculados se devuelven al navegador y no se guardan como pedidos.
