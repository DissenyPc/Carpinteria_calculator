<div class="panel">
    <h3><i class="icon-calculator"></i> Calculadora de Precios y Despiece (Back Office)</h3>
    <p class="text-muted">Selecciona un producto configurado e introduce las dimensiones en milímetros (mm) para calcular el presupuesto interno y despiece de corte.</p>

    <div class="panel">
        <h4>Presupuestos compartidos</h4>
        <div class="row">
            <div class="col-lg-8">
                <select id="calc_quote_id" class="form-control">
                    <option value="">-- Selecciona un presupuesto --</option>
                    {foreach from=$carpinteria_quotes item=quote}
                        <option value="{$quote.id_quote|intval}" {if $selected_quote_id == $quote.id_quote}selected="selected"{/if}>
                            PRES-{$quote.id_quote|string_format:"%06d"} · {$quote.line_count|intval} productos · {$quote.total|string_format:"%.2f"} €
                        </option>
                    {/foreach}
                </select>
            </div>
            <div class="col-lg-4">
                <button type="button" id="btn-create-quote" class="btn btn-default">
                    <i class="icon-plus"></i> Crear presupuesto
                </button>
            </div>
        </div>
        <input type="hidden" id="calc_quote_token" value="{$quote_token|escape:'html':'UTF-8'}">
        <input type="hidden" id="calc_admin_url" value="{$admin_url|escape:'html':'UTF-8'}">
        {if $selected_quote}
            <p class="help-block">Presupuesto seleccionado: <strong>PRES-{$selected_quote.id_quote|string_format:"%06d"}</strong>. Los productos se recalculan con los precios y recetas vigentes al guardar.</p>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Medidas</th>
                            <th>Acabado</th>
                            <th>Materiales</th>
                            <th>Mano de obra</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$carpinteria_quote_lines item=line}
                            <tr>
                                <td>{$line.product_name|default:"Producto"|escape:'html':'UTF-8'}</td>
                                <td>{$line.width|escape:'html':'UTF-8'} × {$line.height|escape:'html':'UTF-8'} mm</td>
                                <td>{$line.color_field_key|escape:'html':'UTF-8'}</td>
                                <td>{$line.materials_cost|string_format:"%.2f"} €</td>
                                <td>{$line.labor_cost|string_format:"%.2f"} €</td>
                                <td>{$line.final_price|string_format:"%.2f"} €</td>
                                <td>
                                    <button type="button" class="btn btn-default btn-sm calc-edit-quote-line"
                                        data-line-id="{$line.id_quote_line|intval}"
                                        data-product-id="{$line.id_product|intval}"
                                        data-width="{$line.width|escape:'html':'UTF-8'}"
                                        data-height="{$line.height|escape:'html':'UTF-8'}"
                                        data-color="{$line.color_field_key|escape:'html':'UTF-8'}"
                                        data-add-overlap="{$line.add_overlap|intval}"
                                        data-overlap-id="{$line.overlap_id_bar|intval}"
                                        data-id-hueco="{$line.id_hueco|intval}"
                                        data-holes="{$line.holes|escape:'html':'UTF-8'}"
                                        data-add-blind="{$line.add_blind|intval}">
                                        Editar
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm calc-delete-quote-line"
                                        data-line-id="{$line.id_quote_line|intval}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        {foreachelse}
                            <tr><td colspan="7">Este presupuesto todavía no tiene productos.</td></tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        {/if}
    </div>

    <div class="form-horizontal">
        <div class="form-group">
            <label class="control-label col-lg-3">Seleccionar Producto</label>
            <div class="col-lg-6">
                <select id="calc_id_product" class="form-control">
                    <option value="">-- Elige un producto --</option>
                    {foreach from=$carpinteria_products item=prod}
                        <option value="{$prod.id_product}">{$prod.name} (ID: {$prod.id_product})</option>
                    {/foreach}
                </select>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">Ancho - W (mm)</label>
            <div class="col-lg-3">
                <input type="number" id="calc_width" class="form-control" placeholder="Ej. 1200" step="1" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">Alto - H (mm)</label>
            <div class="col-lg-3">
                <input type="number" id="calc_height" class="form-control" placeholder="Ej. 1500" step="1" />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">Acabado de Color</label>
            <div class="col-lg-3">
                <select id="calc_color" class="form-control">
                    <option value="price_l_blanco">Blanco Lacado</option>
                    <option value="price_plata">Anodizado Plata</option>
                    <option value="price_col_std">Color Estándar</option>
                    <option value="price_l_color">Color Especial</option>
                    <option value="price_bronce">Bronce</option>
                    <option value="price_inox">Inox</option>
                    <option value="price_madera">Imitación Madera</option>
                    <option value="price_ezzy">Ezzy</option>
                    <option value="price_metal">Metal</option>
                    <option value="price_anod_negro">Anodizado negro</option>
                    <option value="price_bicol01">Bicolor 01</option>
                    <option value="price_bicol08">Bicolor 08</option>
                    <option value="price_bicol09">Bicolor 09</option>
                </select>
            </div>
        </div>

        <hr>

        <div class="form-group">
            <div class="col-lg-9 col-lg-offset-3">
                <label>
                    <input type="checkbox" id="calc_add_overlap" />
                    Añadir solape al perímetro
                </label>
                <select id="calc_overlap_bar" class="form-control">
                    <option value="">-- Selecciona el perfil con solape --</option>
                    {foreach from=$carpinteria_overlap_bars item=bar}
                        <option value="{$bar.id_bar|intval}">{$bar.reference|escape:'html':'UTF-8'} - {$bar.name|escape:'html':'UTF-8'} ({$bar.serie|escape:'html':'UTF-8'})</option>
                    {/foreach}
                </select>
                {if !$carpinteria_overlap_bars}
                    <p class="help-block">No hay perfiles con “solape” en el catálogo de barras. Importa primero Alugom.csv.</p>
                {/if}
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3">Material de los huecos</label>
            <div class="col-lg-6">
                <select id="calc_hueco" class="form-control">
                    <option value="">-- Sin material de hueco --</option>
                    {foreach from=$carpinteria_huecos item=hueco}
                        <option value="{$hueco.id_hueco|intval}">{$hueco.reference|escape:'html':'UTF-8'} - {$hueco.name|escape:'html':'UTF-8'}</option>
                    {/foreach}
                </select>
                {if !$carpinteria_huecos}
                    <p class="help-block">Importa huecos.csv desde el catálogo para habilitar cristal, madera, metacrilato y otros materiales.</p>
                {/if}
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-9 col-lg-offset-3">
                <div id="calc-holes-container"></div>
                <button type="button" id="btn-add-hole" class="btn btn-default">
                    <i class="icon-plus"></i> Añadir hueco
                </button>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-9 col-lg-offset-3">
                <label>
                    <input type="checkbox" id="calc_add_blind" />
                    Añadir persiana (50 €/m²)
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="col-lg-9 col-lg-offset-3">
                <button type="button" id="btn-run-calc" class="btn btn-primary"><i class="icon-check"></i> Calcular Presupuesto y Despiece</button>
            </div>
        </div>
    </div>

    <div id="calc-result-box" style="display: none; margin-top: 25px;">
        <hr>
        <div class="alert alert-success" style="padding: 15px;">
            <h4 style="margin: 0 0 5px 0;">Precio Total Calculado: <strong id="res-final-price">0.00</strong> €</h4>
            <p style="margin: 0; font-size: 13px;">Coste de Materiales: <span id="res-materials-cost">0.00</span> € | Mano de Obra: <span id="res-labor-cost">0.00</span> €</p>
        </div>

        <h4>Despiece para Fabricación y Corte</h4>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Componente</th>
                    <th>Perfil de Barra</th>
                    <th>Cant.</th>
                    <th>Longitud / Área</th>
                    <th>Ángulo de Corte</th>
                    <th>Coste Línea</th>
                </tr>
            </thead>
            <tbody id="res-breakdown-tbody"></tbody>
        </table>
        <button type="button" id="btn-save-quote-line" class="btn btn-success" style="display:none;">
            <i class="icon-save"></i> Añadir producto al presupuesto
        </button>
    </div>

    <div id="calc-error-box" class="alert alert-danger" style="display: none; margin-top: 25px;"></div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var btnCalc = document.getElementById('btn-run-calc');
    var holesContainer = document.getElementById('calc-holes-container');
    var addHoleButton = document.getElementById('btn-add-hole');
    var saveQuoteLineButton = document.getElementById('btn-save-quote-line');
    var editingLineId = 0;
    var hasCalculated = false;

    function getAdminUrl(quoteId) {
        var url = document.getElementById('calc_admin_url').value;
        return url + (url.indexOf('?') === -1 ? '?' : '&') + 'id_quote=' + encodeURIComponent(quoteId);
    }

    function buildCalculationPayload() {
        var holes = Array.prototype.map.call(holesContainer.querySelectorAll('.calc-hole-row'), function(row) {
            return {
                width: row.querySelector('.calc-hole-width').value,
                height: row.querySelector('.calc-hole-height').value
            };
        });

        return new URLSearchParams({
            ajax: 1,
            id_product: document.getElementById('calc_id_product').value,
            width: document.getElementById('calc_width').value,
            height: document.getElementById('calc_height').value,
            color_field_key: document.getElementById('calc_color').value,
            add_overlap: document.getElementById('calc_add_overlap').checked ? 1 : 0,
            overlap_id_bar: document.getElementById('calc_overlap_bar').value,
            id_hueco: document.getElementById('calc_hueco').value,
            holes: JSON.stringify(holes),
            add_blind: document.getElementById('calc_add_blind').checked ? 1 : 0
        });
    }

    function postQuoteAction(payload) {
        payload.set('quote_token', document.getElementById('calc_quote_token').value);
        return fetch('{$ajax_url}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: payload
        }).then(function(response) {
            return response.json();
        });
    }

    document.getElementById('calc_quote_id').addEventListener('change', function() {
        editingLineId = 0;
        saveQuoteLineButton.textContent = 'Añadir producto al presupuesto';
        saveQuoteLineButton.style.display = hasCalculated && this.value ? 'inline-block' : 'none';
        if (this.value) {
            window.location.href = getAdminUrl(this.value);
        }
    });

    document.getElementById('btn-create-quote').addEventListener('click', function() {
        var payload = new URLSearchParams({ quote_action: 'create_quote' });
        postQuoteAction(payload).then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'No se pudo crear el presupuesto.');
            }
            window.location.href = getAdminUrl(data.id_quote);
        }).catch(function(error) {
            window.alert(error.message);
        });
    });

    document.querySelectorAll('.calc-edit-quote-line').forEach(function(button) {
        button.addEventListener('click', function() {
            var data = button.dataset;
            editingLineId = parseInt(data.lineId, 10);
            document.getElementById('calc_id_product').value = data.productId;
            document.getElementById('calc_width').value = data.width;
            document.getElementById('calc_height').value = data.height;
            document.getElementById('calc_color').value = data.color;
            document.getElementById('calc_add_overlap').checked = data.addOverlap === '1';
            document.getElementById('calc_overlap_bar').value = data.overlapId;
            document.getElementById('calc_hueco').value = data.idHueco;
            document.getElementById('calc_add_blind').checked = data.addBlind === '1';
            holesContainer.textContent = '';
            try {
                JSON.parse(data.holes || '[]').forEach(function(hole) {
                    addHoleRow();
                    var row = holesContainer.lastElementChild;
                    row.querySelector('.calc-hole-width').value = hole.width;
                    row.querySelector('.calc-hole-height').value = hole.height;
                });
            } catch (error) {
                window.alert('No se pudieron cargar las medidas de los huecos guardadas.');
                return;
            }
            saveQuoteLineButton.textContent = 'Actualizar producto del presupuesto';
            document.getElementById('calc_id_product').scrollIntoView({ behavior: 'smooth' });
        });
    });

    document.querySelectorAll('.calc-delete-quote-line').forEach(function(button) {
        button.addEventListener('click', function() {
            var quoteId = document.getElementById('calc_quote_id').value;
            if (!window.confirm('¿Eliminar este producto del presupuesto?')) {
                return;
            }
            var payload = new URLSearchParams({
                quote_action: 'delete_line',
                quote_token: document.getElementById('calc_quote_token').value,
                id_quote: quoteId,
                id_quote_line: button.dataset.lineId
            });
            postQuoteAction(payload).then(function(data) {
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo eliminar el producto.');
                }
                window.location.href = getAdminUrl(quoteId);
            }).catch(function(error) {
                window.alert(error.message);
            });
        });
    });

    function addHoleRow() {
        var row = document.createElement('div');
        row.className = 'row calc-hole-row';
        row.style.marginBottom = '8px';
        row.innerHTML =
            '<div class="col-xs-4"><input type="number" class="form-control calc-hole-width" min="1" step="1" placeholder="Ancho del hueco (mm)" /></div>' +
            '<div class="col-xs-4"><input type="number" class="form-control calc-hole-height" min="1" step="1" placeholder="Alto del hueco (mm)" /></div>' +
            '<div class="col-xs-4"><button type="button" class="btn btn-danger calc-remove-hole">Quitar</button></div>';
        holesContainer.appendChild(row);
    }

    addHoleButton.addEventListener('click', addHoleRow);
    holesContainer.addEventListener('click', function(event) {
        if (event.target.classList.contains('calc-remove-hole')) {
            event.target.closest('.calc-hole-row').remove();
        }
    });

    if (btnCalc) {
        btnCalc.addEventListener('click', function() {
            document.getElementById('calc-error-box').style.display = 'none';

            if (!document.getElementById('calc_id_product').value) {
                alert('Por favor, seleccione un producto.');
                return;
            }

            fetch('{$ajax_url}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: buildCalculationPayload()
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('res-final-price').textContent = data.final_price;
                    document.getElementById('res-materials-cost').textContent = data.materials_cost;
                    document.getElementById('res-labor-cost').textContent = data.labor_cost;

                    var tbody = document.getElementById('res-breakdown-tbody');
                    tbody.innerHTML = '';
                    data.breakdown.forEach(function(item) {
                        var tr = document.createElement('tr');
                        [
                            item.part_label,
                            (item.bar_ref ? item.bar_ref + ' - ' : '') + (item.bar_name || ''),
                            item.quantity,
                            item.length_mm ? item.length_mm + ' mm' : (item.area_m2 ? item.area_m2 + ' m²' : ''),
                            item.cut_angle || '',
                            item.line_cost + ' €'
                        ].forEach(function(value) {
                            var cell = document.createElement('td');
                            cell.textContent = value;
                            tr.appendChild(cell);
                        });
                        tbody.appendChild(tr);
                    });

                    document.getElementById('calc-result-box').style.display = 'block';
                    hasCalculated = true;
                    saveQuoteLineButton.style.display = document.getElementById('calc_quote_id').value ? 'inline-block' : 'none';
                    saveQuoteLineButton.textContent = editingLineId ? 'Actualizar producto del presupuesto' : 'Añadir producto al presupuesto';
                } else {
                    document.getElementById('calc-result-box').style.display = 'none';
                    document.getElementById('calc-error-box').textContent = data.message;
                    document.getElementById('calc-error-box').style.display = 'block';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                document.getElementById('calc-result-box').style.display = 'none';
                document.getElementById('calc-error-box').textContent = 'No se pudo completar el cálculo. Comprueba la conexión e inténtalo de nuevo.';
                document.getElementById('calc-error-box').style.display = 'block';
            });
        });
    }

    saveQuoteLineButton.addEventListener('click', function() {
        var quoteId = document.getElementById('calc_quote_id').value;
        if (!quoteId) {
            window.alert('Crea o selecciona un presupuesto antes de añadir el producto.');
            return;
        }

        var payload = buildCalculationPayload();
        payload.set('quote_action', editingLineId ? 'update_line' : 'add_line');
        payload.set('quote_token', document.getElementById('calc_quote_token').value);
        payload.set('id_quote', quoteId);
        if (editingLineId) {
            payload.set('id_quote_line', editingLineId);
        }

        postQuoteAction(payload).then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'No se pudo guardar el producto en el presupuesto.');
            }
            window.location.href = getAdminUrl(quoteId);
        }).catch(function(error) {
            window.alert(error.message);
        });
    });
});
</script>