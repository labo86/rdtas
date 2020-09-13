<?php
declare(strict_types=1);

namespace labo86\rdtas\staty;


use labo86\staty\Block;

/**
 * Implementación de auto service front end.
 * Genera un front-end para web services de una salida dada por el retorno de un web service get_automated_method_list {@see registerAutomaticMethodService()}
 * Esta hecho para soportar un template CSS basado en labo86. como ejemplo ver el proyecto {@see https://github.com/labo86/mpanager mpanager}
 * Se debe setear el metodo {@see setService()}
 * @package labo86\staty
 */
class BlockPageEasyServices extends Block
{
    protected string $services;

    protected array $custom_method_form_list = [];

    public function getTitle() : string {
        return $this->page->getMetadata()['title'] ?? '';
    }

    public function getDescription() : string {
        return $this->page->getMetadata()['description'] ?? '';
    }

    public function setService(string $service) {
        $this->service = $service;
    }

    public function getService() {
        return $this->service;
    }

    public function sectionBeginForm($method) {
        $form_data = [
                'method' => $method,
                'id' => $method . '_custom'
        ];

        $this->custom_method_form_list[] = $form_data;
        $this->sectionBegin($form_data['id']);
    }

    public function sectionHeadAdditional() {
        $this->sectionBegin('head_additional');
    }

    public function html() {
        $this->sectionEnd();
        ?>
        <!doctype html>
        <html lang="es">
        <head>
            <?php $this->htmlHeadCommon() ?>
            <title><?=$this->getTitle()?></title>
            <?=$this->getSectionContent('head_additional')?>
        </head>
        <body>
        <div style="display:flex;height:100%;flex-direction:column">
            <div style="background-color:white; flex-grow:1; border-bottom-color:white;border-bottom-style:double;">
                <?php $this->htmlBodyContent(); ?>
            </div>
            <footer class="section-container">
                <div class="container-padding" style="text-align:center">
                    <img width=100 src="<?=$this->makeLabo86Logo()?>">
                    <p style="color:#7f7f7f;font-size:0.7em">Edwin Rodríguez-León © <?=date('Y')?></p>
                </div>
            </footer>
        </div>
        </body>
        </html>
        <?php
    }

    public function htmlHeadCommon() { ?>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <script src="https://unpkg.com/@labo86/sero@latest/dist/sero.min.js"></script>
        <script src="https://unpkg.com/@labo86/rdtas@latest/dist/rdtas.min.js"></script>
        <?php
    }


    public function htmlBodyContent() { ?>
<div id="main-container" class="section-container" style="text-align:center">
        <div class="container-padding" data-page-name="index_page">
            <h2>Servicios personalizados</h2>
            <?php foreach ( $this->custom_method_form_list as $form_data ) :
                $method = $form_data['method'];
                $id = $form_data['id'];
                ?>
                <button onclick="changePage('<?=$id?>_page')"><?=$method?></button><br/>
            <?php endforeach; ?>
            <h2>Servicios automaticos disponibles</h2>
            <div id="automatic_buttons"></div>
        </div>
    <?php foreach ( $this->custom_method_form_list as $form_data ) :
        $method = $form_data['method'];
        $id = $form_data['id'];
        ?>
        <div class="container-padding" data-page-name="<?=$id?>_page" style="display:none">
            <h2><?=$method?></h2>
            <form id="<?=$id?>_form">
            <?=$this->getSectionContent($id)?>
                <br/>
                <input type="hidden" name="method" value="<?=$method?>">
                <button onclick="submitRequest('<?=$id?>_form')">Enviar</button>
            </form>
            <button onclick="changePage('index_page')">Back</button>
        </div>
    <?php endforeach; ?>
</div>
<script>
const endpoint = '<?=$this->getService()?>';

fetch(endpoint)
.then(response  => response.json())
.then(function(myJson) {

    let html = "";
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
        "            <button onclick=\"submitRequest('" + automatic_method.endpoint + "', '"+ automatic_method.method + "_form')\">Enviar</button>\n" +
        "        </form>\n" +
        "        <button onclick=\"changePage('index_page')\">Back</button>\n" +
        "    </div>";
    }

    let html_automatic_buttons = "";
    for ( let automatic_method of myJson )
        if ( automatic_method.parameter_list.length > 0 ) {
            html_automatic_buttons += "        <button onclick=\"changePage('" + automatic_method.method + "_page')\">" + automatic_method.method + "</button><br/>";
        } else {
            html_automatic_buttons += "        <button onclick=\"submitRequestGet('" + automatic_method.endpoint + "', 'method=" + automatic_method.method + "')\">" + automatic_method.method + "</button><br/>";
    }
    document.getElementById('automatic_buttons').innerHTML = html_automatic_buttons;

    rdtas.appendToInnerHtml(document.getElementById('main-container'), html);

    let url = new URL(window.location);
    let params = new URLSearchParams(url.search);
    if ( params.has('method') ) {
        changePage(params.get('method') + '_page');
        sero.get(params.get('method') + '_form').value = Object.fromEntries(params);
    }


});

function changePage(page_id) {
    rdtas.switchVisibility(document.getElementById('main-container'),page_id);
}

function submitRequest(endpoint, form_id) {
    event.preventDefault();
    fetch(endpoint, {
        method: 'POST',
        body: new FormData(document.getElementById(form_id))
    })
    .then( res => res.blob() )
    .then( blob => {
        rdtas.openBlobInNewWindow(blob);
    });
}

function submitRequestGet(endpoint, query_params) {
    event.preventDefault();
    fetch(endpoint + "?" + query_params )
    .then( res => res.blob() )
    .then( blob => {
        rdtas.openBlobInNewWindow(blob);
    });
}
</script>
    <?php
    }
}