{if $is_employee}
<div class="carpinteria-calculator-box card card-body mb-3">
    <h4 class="card-title">Calculadora de precio y despiece</h4>
    <p class="text-muted">Introduce las medidas en milímetros para obtener un cálculo interno.</p>

    <div class="row">
        <div class="col-md-4 form-group">
            <label for="carpinteria_w">Ancho (mm)</label>
            <input type="number" id="carpinteria_w" class="form-control"
                min="{$config.min_width|escape:'html':'UTF-8'}"
                max="{$config.max_width|escape:'html':'UTF-8'}" step="1">
        </div>
        <div class="col-md-4 form-group">
            <label for="carpinteria_h">Alto (mm)</label>
            <input type="number" id="carpinteria_h" class="form-control"
                min="{$config.min_height|escape:'html':'UTF-8'}"
                max="{$config.max_height|escape:'html':'UTF-8'}" step="1">
        </div>
        <div class="col-md-4 form-group">
            <label for="carpinteria_color">Acabado</label>
            <select id="carpinteria_color" class="form-control">
                <option value="price_l_blanco">Blanco lacado</option>
                <option value="price_plata">Anodizado plata</option>
                <option value="price_col_std">Color estándar</option>
                <option value="price_l_color">Color especial</option>
                <option value="price_bronce">Bronce</option>
                <option value="price_inox">Inox</option>
                <option value="price_madera">Imitación madera</option>
                <option value="price_ezzy">Ezzy</option>
                <option value="price_metal">Metal</option>
                <option value="price_anod_negro">Anodizado negro</option>
                <option value="price_bicol01">Bicolor 01</option>
                <option value="price_bicol08">Bicolor 08</option>
                <option value="price_bicol09">Bicolor 09</option>
            </select>
        </div>
    </div>

    <button type="button" id="btn-calculate-carpinteria" class="btn btn-primary">
        Calcular precio y despiece
    </button>
    <div id="carpinteria-error" class="alert alert-danger mt-3" style="display:none;"></div>
    <div id="carpinteria-result" class="mt-3" style="display:none;">
        <div class="alert alert-success">
            <strong>Precio total: <span id="res-final-price">0.00</span> €</strong><br>
            Materiales: <span id="res-materials-cost">0.00</span> € |
            Mano de obra: <span id="res-labor-cost">0.00</span> €
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Componente</th>
                        <th>Perfil</th>
                        <th>Cantidad</th>
                        <th>Longitud</th>
                        <th>Corte</th>
                        <th>Coste</th>
                    </tr>
                </thead>
                <tbody id="res-breakdown-body"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var button = document.getElementById('btn-calculate-carpinteria');
    if (!button) {
        return;
    }

    button.addEventListener('click', function() {
        var errorBox = document.getElementById('carpinteria-error');
        var resultBox = document.getElementById('carpinteria-result');
        errorBox.style.display = 'none';

        var body = new URLSearchParams({
            ajax: 1,
            id_product: '{$id_product|intval}',
            width: document.getElementById('carpinteria_w').value,
            height: document.getElementById('carpinteria_h').value,
            color_field_key: document.getElementById('carpinteria_color').value
        });

        fetch('{$ajax_url|escape:'javascript':'UTF-8'}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (!data.success) {
                throw new Error(data.message || 'No se pudo calcular el precio.');
            }

            document.getElementById('res-final-price').textContent = data.final_price;
            document.getElementById('res-materials-cost').textContent = data.materials_cost;
            document.getElementById('res-labor-cost').textContent = data.labor_cost;

            var tbody = document.getElementById('res-breakdown-body');
            tbody.textContent = '';
            data.breakdown.forEach(function(item) {
                var row = document.createElement('tr');
                [
                    item.part_label,
                    (item.bar_ref ? item.bar_ref + ' - ' : '') + (item.bar_name || ''),
                    item.quantity,
                    item.length_mm + ' mm',
                    item.cut_angle || '',
                    item.line_cost + ' €'
                ].forEach(function(value) {
                    var cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });
                tbody.appendChild(row);
            });

            resultBox.style.display = 'block';
        })
        .catch(function(error) {
            resultBox.style.display = 'none';
            errorBox.textContent = error.message;
            errorBox.style.display = 'block';
        });
    });
});
</script>
{/if}
