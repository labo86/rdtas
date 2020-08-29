<?php
declare(strict_types=1);

namespace labo86\rdtas\staty;


use labo86\staty\Block;

/**
 * Implementación de auto service front end.
 * Genera un front-end para web services de una salida dada por el retorno de un web service get_automated_method_list {@see registerAutomaticMethodService()}
 *
 * Requiere que se incluya en los header la biblioteca sero {@see https://github.com/labo86/sero}
 * <code>
 * <script src="https://unpkg.com/@labo86/sero@latest/dist/sero.min.js"></script>
 * </code>
 *
 * Esta hecho para soportar un template CSS basado en labo86. como ejemplo ver el proyecto {@see https://github.com/labo86/mpanager mpanager}
 * Se debe setear el metodo {@see setService()}
 * @package labo86\staty
 */
class BlockAutoServices extends Block
{
    protected string $services;

    public function setService(string $service) {
        $this->service = $service;
    }

    public function getService() {
        return $this->service;
    }

    public function html() {?>
<div id="main-container" class="section-container" style="text-align:center">
</div>
<script>
const endpoint = '<?=$this->getService()?>';

fetch(endpoint + "?method=get_automatic_method_list")
.then(response  => response.json())
.then(function(myJson) {
            let html = "    <div class=\"container-padding\" data-page-name=\"index_page\">\n" +
                "        <h2>Servicios disponibles</h2>\n";

    for ( let automatic_method of myJson )
        if ( automatic_method.parameter_list.length > 0 ) {
            html += "        <button onclick=\"changePage('" + automatic_method.method + "_page')\">" + automatic_method.method + "</button><br/>";
        } else {
            html += "        <button onclick=\"submitRequestGet('method=" + automatic_method.method + "')\">" + automatic_method.method + "</button><br/>";
        }

    html += "    </div>";

    for ( let automatic_method of myJson ) {
                if ( automatic_method.parameter_list.length === 0 ) continue;
                html += "    <div class=\"container-padding\" data-page-name=\"" + automatic_method.method + "_page\" style=\"display:none\">\n" +
                    "        <h2>" + automatic_method.method +"</h2>\n" +
                    "        <form id=\"" + automatic_method.method +"_form\">\n";

                for ( let parameter of automatic_method.parameter_list) {
                    html += "<label>" + parameter.name + "</label>";
                    if ( parameter.type === 'labo86\\hapi\\InputFile' ) {
                        html += "<input type=\"file\" name=\"" + parameter.name + "\">";
                    } else if ( parameter.type === 'labo86\\hapi\\InputFileList') {
                        html += "<input type=\"file\" name=\"" + parameter.name + "\" multiple>";
                    } else if ( parameter.type === 'string') {
                        html += "<input type=\"text\" name=\"" + parameter.name + "\">";
                    } else if ( parameter.type === 'int') {
                        html += "<input type=\"text\" name=\"" + parameter.name + "\">";
                    }
                    html += "<br/>";
                }

        html +=
            "            <input type=\"hidden\" name=\"method\" value=\""+ automatic_method.method + "\">\n" +
            "            <button onclick=\"submitRequest('"+ automatic_method.method + "_form')\">Enviar</button>\n" +
            "        </form>\n" +
            "        <button onclick=\"changePage('index_page')\">Back</button>\n" +
            "    </div>";
    }

    document.getElementById('main-container').innerHTML = html;

    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);
    if ( params.has('method') ) {
        changePage(params.get('method') + '_page');
        sero.get(params.get('method') + '_form').value = Object.fromEntries(params);
    }


});

function switchVisibility(element, name) {
    for ( let child of element.children ) {
        if ( !child.hasAttribute('data-page-name') )
            child.style.display = 'none';
        else if ( child.getAttribute('data-page-name') === name ) {
            child.style.display = null;
        } else {
            child.style.display = 'none';
        }
    }
}

function changePage(page_id) {
    switchVisibility(document.getElementById('main-container'),page_id);
}

function submitRequest(form_id) {
    event.preventDefault();
    fetch(endpoint, {
        method: 'POST',
        body: new FormData(document.getElementById(form_id))
    })
    .then( res => res.blob() )
    .then( blob => {
        let file = window.URL.createObjectURL(blob);
        window.open(file);
    });
}

function submitRequestGet(query_params) {
    event.preventDefault();
    fetch(endpoint + "?" + query_params )
    .then( res => res.blob() )
        .then( blob => {
        let file = window.URL.createObjectURL(blob);
            window.open(file);
        });
}
</script>
    <?php
    }
}