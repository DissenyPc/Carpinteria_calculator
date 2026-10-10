<div class="panel product-tab">
    <h3><i class="icon-cogs"></i> Configuración de Carpintería Metálica (Medidas y Receta)</h3>

    <div class="form-group">
        <h4>Restricciones de Medidas (en milímetros - mm)</h4>
        <div class="row">
            <div class="col-lg-3">
                <label>Ancho Mínimo (mm)</label>
                <input type="number" step="1" name="carpinteria_min_width" value="{$carpinteria_config.min_width|escape:'html':'UTF-8'}" class="form-control" />
            </div>
            <div class="col-lg-3">
                <label>Ancho Máximo (mm)</label>
                <input type="number" step="1" name="carpinteria_max_width" value="{$carpinteria_config.max_width|escape:'html':'UTF-8'}" class="form-control" />
            </div>
            <div class="col-lg-3">
                <label>Alto Mínimo (mm)</label>
                <input type="number" step="1" name="carpinteria_min_height" value="{$carpinteria_config.min_height|escape:'html':'UTF-8'}" class="form-control" />
            </div>
            <div class="col-lg-3">
                <label>Alto Máximo (mm)</label>
                <input type="number" step="1" name="carpinteria_max_height" value="{$carpinteria_config.max_height|escape:'html':'UTF-8'}" class="form-control" />
            </div>
        </div>
    </div>

    <div class="form-group">
        <h4>Costes de Fabricación</h4>
        <div class="row">
            <div class="col-lg-4">
                <label>Coste Fijo de Mano de Obra (€)</label>
                <div class="input-group">
                    <input type="text" name="carpinteria_assembly_labor_cost" value="{$carpinteria_config.assembly_labor_cost|escape:'html':'UTF-8'}" class="form-control" />
                    <span class="input-group-addon">€</span>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="form-group">
        <h4>Receta de Despiece y Fórmulas</h4>
        <p class="help-block">Usa variables como <strong>W</strong> (Ancho) y <strong>H</strong> (Alto). Ej: <code>H</code>, <code>W - 45</code>, <code>(W / 2) + 12</code>.</p>
        
        <table class="table table-bordered" id="recipe-table">
            <thead>
                <tr>
                    <th>Componente / Etiqueta</th>
                    <th>Barra del Catálogo</th>
                    <th>Cantidad</th>
                    <th>Fórmula de Longitud</th>
                    <th>Ángulo de Corte</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$carpinteria_recipe item=row}
                <tr>
                    <td><input type="text" name="carpinteria_part_label[]" value="{$row.part_label|escape:'html':'UTF-8'}" class="form-control" /></td>
                    <td>
                        <select name="carpinteria_id_bar[]" class="form-control">
                            {foreach from=$carpinteria_bars item=bar}
                                <option value="{$bar.id_bar}" {if $row.id_bar ==$bar.id_bar}selected="selected"{/if}>{$bar.reference} - {$bar.name}</option>
                            {/foreach}
                        </select>
                    </td>
                    <td><input type="number" name="carpinteria_quantity[]" value="{$row.quantity}" class="form-control" style="width: 80px;" /></td>
                    <td><input type="text" name="carpinteria_formula_len[]" value="{$row.formula_len|escape:'html':'UTF-8'}" class="form-control" /></td>
                    <td><input type="text" name="carpinteria_cut_angle[]" value="{$row.cut_angle|escape:'html':'UTF-8'}" class="form-control" style="width: 100px;" /></td>
                    <td><button type="button" class="btn btn-danger delete-row"><i class="icon-trash"></i></button></td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        <button type="button" id="add-recipe-row" class="btn btn-default"><i class="icon-plus"></i> Añadir Componente</button>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('add-recipe-row').addEventListener('click', function() {
        var tbody = document.querySelector('#recipe-table tbody');
        var newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td><input type="text" name="carpinteria_part_label[]" value="" class="form-control" placeholder="Ej. Marco Vertical" /></td>
            <td>
                <select name="carpinteria_id_bar[]" class="form-control">
                    {foreach from=$carpinteria_bars item=bar}
                        <option value="{$bar.id_bar}">{$bar.reference} - {$bar.name}</option>
                    {/foreach}
                </select>
            </td>
            <td><input type="number" name="carpinteria_quantity[]" value="1" class="form-control" style="width: 80px;" /></td>
            <td><input type="text" name="carpinteria_formula_len[]" value="H" class="form-control" /></td>
            <td><input type="text" name="carpinteria_cut_angle[]" value="90-90" class="form-control" style="width: 100px;" /></td>
            <td><button type="button" class="btn btn-danger delete-row"><i class="icon-trash"></i></button></td>
        `;
        tbody.appendChild(newRow);
    });

    document.addEventListener('click', function(e) {
        if (e.target && (e.target.classList.contains('delete-row') || e.target.closest('.delete-row'))) {
            var row = e.target.closest('tr');
            row.remove();
        }
    });
});
</script>