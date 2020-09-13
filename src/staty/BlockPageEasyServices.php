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
        <div>
            <div style="background-color:white; border-bottom-color:white;border-bottom-style:double;">
                <?php $this->htmlBodyContent(); ?>
            </div>
            <footer class="section-container">
                <div class="container-padding" style="text-align:center">
                    <img width=100 src="<?=$this->makeLabo86Logo()?>">
                    <p style="color:#7f7f7f;font-size:0.7em">Edwin Rodríguez-León © <?=date('Y')?></p>
                </div>
            </footer>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
        </body>
        </html>
        <?php
    }

    public function htmlHeadCommon() { ?>
        <meta charset="UTF-8">
        <meta name="viewport"
              content="width=device-width, initial-scale=1.0, minimum-scale=1.0, shrink-to-fit=no">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <script src="https://unpkg.com/@labo86/sero@latest/dist/sero.min.js"></script>
        <script src="https://unpkg.com/@labo86/rdtas@latest/dist/rdtas.min.js"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
        <?php
    }

    public function htmlBodyContent() { ?>
<div id="main-container">
        <div class="container-md" data-page-name="index_page">
            <h2>Servicios personalizados</h2>
            <?php foreach ( $this->custom_method_form_list as $form_data ) :
                $method = $form_data['method'];
                $id = $form_data['id'];
                ?>
                <button onclick="changePage('<?=$id?>_page')"><?=$method?></button><br/>
            <?php endforeach; ?>
            <h2>Servicios automaticos disponibles</h2>
            <div id="automatic_buttons" class="list-group">
            </div>
        </div>
    <?php foreach ( $this->custom_method_form_list as $form_data ) :
        $method = $form_data['method'];
        $id = $form_data['id'];
        ?>
        <div class="container-md" data-page-name="<?=$id?>_page" style="display:none">
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
            html += "    <div class=\"container-md\" data-page-name=\"" + automatic_method.method + "_page\" style=\"display:none\">" +
                "        <h2>" + automatic_method.method +"</h2>" +
                "       <button onclick=\"changePage('index_page')\">Back</button>" +
                "        <form id=\"" + automatic_method.method +"_form\">";

            for ( let parameter of automatic_method.parameter_list) {
                html += "<div class=\"form-group\">";
                html += "<label>" + parameter.name + "</label>";
                if ( parameter.type === 'labo86\\hapi\\InputFile' ) {
                    html += "<input class=\"form-control\" type=\"file\" name=\"" + parameter.name + "\">";
                } else if ( parameter.type === 'labo86\\hapi\\InputFileList') {
                    html += "<input class=\"form-control\" type=\"file\" name=\"" + parameter.name + "\" multiple>";
                } else if ( parameter.type === 'string') {
                    html += "<input class=\"form-control\" type=\"text\" name=\"" + parameter.name + "\">";
                } else if ( parameter.type === 'int') {
                    html += "<input class=\"form-control\" type=\"text\" name=\"" + parameter.name + "\">";
                }
                html += "</div>";
            }

        html +=
        "            <input type=\"hidden\" name=\"method\" value=\""+ automatic_method.method + "\">" +
        "            <button type=\"submit\" class=\"btn btn-primary\" onclick=\"submitRequest('" + automatic_method.endpoint + "', '"+ automatic_method.method + "_form', '"+ automatic_method.method +"')\">Enviar</button>" +
        "        </form>" +
        "  <iframe id=\"" + automatic_method.method + "_target\"></iframe>" +
        "        <button onclick=\"newWindow('" + automatic_method.method + "')\">New Window</button>" +
        "    </div>";
    }

    let html_automatic_buttons = "";
    for ( let automatic_method of myJson )
        if ( automatic_method.parameter_list.length > 0 ) {
            html_automatic_buttons += "        <button  type=\"button\" class=\"list-group-item list-group-item-action\" onclick=\"changePage('" + automatic_method.method + "_page')\">" + automatic_method.method + "</button>";
        } else {
            html_automatic_buttons += "        <button  type=\"button\" class=\"list-group-item list-group-item-action\" onclick=\"submitRequestGet('" + automatic_method.endpoint + "', 'method=" + automatic_method.method + "')\">" + automatic_method.method + "</button>";
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

function newWindow(method) {
    let src = document.getElementById(method + "_target").src;
    window.open(src);
}

function submitRequest(endpoint, form_id, method) {
    event.preventDefault();
    fetch(endpoint, {
        method: 'POST',
        body: new FormData(document.getElementById(form_id))
    })
    .then( res => res.blob() )
    .then( blob => {
        let file = window.URL.createObjectURL(blob);
        let frame = document.getElementById(method + "_target");
        frame.src = file;
        //rdtas.openBlobInNewWindow(blob);
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