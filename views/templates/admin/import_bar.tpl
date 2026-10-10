<div class="panel">
    <div class="panel-heading">
        <i class="icon-upload"></i> Importador de catálogo de barras
    </div>

    {if $confirmation_message}
        <div class="alert {if $error_message}alert-danger{else}alert-success{/if}">
            {$confirmation_message|escape:'html':'UTF-8'}
        </div>
    {/if}

    <form action="{$import_url|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="carpinteria_csv_file">Archivo CSV</label>
            <input
                type="file"
                id="carpinteria_csv_file"
                name="carpinteria_csv_file"
                accept=".csv,text/csv"
                class="form-control"
                required
            >
            <p class="help-block">
                El archivo debe usar punto y coma (;) como separador. Se espera una columna de referencia,
                nombre, serie y los precios de los acabados.
            </p>
        </div>

        <button type="submit" name="submitImportCsv" class="btn btn-primary">
            <i class="icon-upload"></i> Importar barras
        </button>
    </form>

    <hr>

    <h3>Importador de materiales para huecos</h3>
    <p class="help-block">Los precios del catálogo se interpretan como €/m² y se aplicará el acabado seleccionado en el calculador.</p>
    <form action="{$import_url|escape:'html':'UTF-8'}" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="huecos_csv_file">Archivo CSV de huecos</label>
            <input
                type="file"
                id="huecos_csv_file"
                name="huecos_csv_file"
                accept=".csv,text/csv"
                class="form-control"
            >
        </div>
        <button type="submit" name="submitUploadHuecosCsv" class="btn btn-primary">
            <i class="icon-upload"></i> Importar archivo seleccionado
        </button>
    </form>

    <h4>Materiales importados para huecos</h4>
    {if $huecos_list}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Material</th>
                        <th>Serie</th>
                        <th>Plata (€/m²)</th>
                        <th>Blanco (€/m²)</th>
                        <th>Color estándar (€/m²)</th>
                        <th>Color especial (€/m²)</th>
                        <th>Bronce (€/m²)</th>
                        <th>Inox (€/m²)</th>
                        <th>Madera (€/m²)</th>
                        <th>Ezzy (€/m²)</th>
                        <th>Metal (€/m²)</th>
                        <th>Anodizado negro (€/m²)</th>
                        <th>Bicolor 01 (€/m²)</th>
                        <th>Bicolor 08 (€/m²)</th>
                        <th>Bicolor 09 (€/m²)</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$huecos_list item=hueco}
                        <tr>
                            <td>{$hueco.reference|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.name|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.serie|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_plata|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_l_blanco|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_col_std|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_l_color|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_bronce|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_inox|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_madera|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_ezzy|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_metal|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_anod_negro|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_bicol01|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_bicol08|escape:'html':'UTF-8'}</td>
                            <td>{$hueco.price_bicol09|escape:'html':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <p class="alert alert-info">Todavía no hay materiales para huecos. Importa huecos.csv para añadirlos.</p>
    {/if}

    <hr>

    <h3>Catálogo de barras</h3>
    {if $bars_list}
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Referencia</th>
                        <th>Nombre</th>
                        <th>Serie</th>
                        <th>Plata</th>
                        <th>Blanco lacado</th>
                        <th>Color estándar</th>
                        <th>Color especial</th>
                        <th>Bronce</th>
                        <th>Inox</th>
                        <th>Madera</th>
                        <th>Ezzy</th>
                        <th>Metal</th>
                        <th>Anodizado negro</th>
                        <th>Bicolor 01</th>
                        <th>Bicolor 08</th>
                        <th>Bicolor 09</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$bars_list item=bar}
                        <tr>
                            <td>{$bar.reference|escape:'html':'UTF-8'}</td>
                            <td>{$bar.name|escape:'html':'UTF-8'}</td>
                            <td>{$bar.serie|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_plata|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_l_blanco|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_col_std|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_l_color|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_bronce|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_inox|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_madera|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_ezzy|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_metal|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_anod_negro|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_bicol01|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_bicol08|escape:'html':'UTF-8'}</td>
                            <td>{$bar.price_bicol09|escape:'html':'UTF-8'}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {else}
        <p class="alert alert-info">Todavía no hay barras en el catálogo. Importa un CSV para añadirlas.</p>
    {/if}
</div>
