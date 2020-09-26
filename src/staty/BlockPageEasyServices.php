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

    protected array $link_list = [];

    protected array $custom_page_list = [];

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

    public function addLink(string $name, string $target) {
        $this->link_list[] = [
                'name' => $name,
                'target' => $target
        ];
    }

    /**
     * Use los siguientes widgets para:
     * Page volver
     * <code>
     * <button class="btn btn-outline-secondary btn-sm" onclick="changePage('index_page')">Volver</button>
     * </code>
     * Para cambiar de visibilidad
     * <code>
     *     rdtas.switchVisibility(document.getElementById('main-container'),page_id);
     * </code>
     * Para hacer post
     * <code>
     * fetch(endpoint)
     *   .then(response  => response.json())
     *   .then(function(json) {
     *   }
     *
     * </code>
     * @param string $page_name
     */
    public function sectionBeginPage(string $page_name) {
        $page_data = [
                'id' => $page_name . '_page_custom',
                'name' => $page_name
        ];

        $this->custom_page_list[] = $page_data;
        $this->sectionBegin($page_data['id']);
    }

    public function sectionBeginForm(string $method, string $endpoint) {
        $form_data = [
                'method' => $method,
                'id' => $method . '_form_custom',
                'endpoint' => $endpoint
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
        <body style="background-color:black">
        <div>
            <div style="background-color:white; border-bottom-color:white;border-bottom-style:double;">
                <nav class="navbar navbar-light bg-light">
                    <span class="navbar-brand">
                        <img src="http://www.labo86.cl/assets/images/labo86_black_letter_200x64.png" width="100" class="d-inline-block" alt="" loading="lazy">
                        <?=$this->getTitle()?>
                    </span>
                </nav>
                <?php $this->htmlBodyContent(); ?>
            </div>

            <footer>
                <div class="mt-3" style="text-align:center">
                    <img width=100 src="http://www.labo86.cl/assets/images/labo86_white_letter_200x64.png">
                    <p style="color:#7f7f7f;">Edwin Rodríguez-León © <?=date('Y')?></p>
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
            <p><?=$this->getDescription()?></p>
            <?php if ( !empty($this->custom_method_form_list) || !empty($this->link_list) || !empty($this->custom_page_list) ) : ?>
            <h4>Servicios</h4>
            <div class="container-fluid btn-group-vertical mb-5">
                <?php foreach ( $this->link_list as $link ) :
                    $name = $link['name'];
                    $target = $link['target'];
                    ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.open('<?=$target?>')"><?=$name?></button>
                <?php endforeach; ?>
                <?php foreach ( $this->custom_page_list as $page_data ) :
                    $name = $page_data['name'];
                    $id = $page_data['id'];
                    ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changePage('<?=$id?>_page')"><?=$name?></button>
                <?php endforeach; ?>
                <?php foreach ( $this->custom_method_form_list as $form_data ) :
                    $method = $form_data['method'];
                    $id = $form_data['id'];
                    ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changePage('<?=$id?>_page')"><?=$method?></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            <h4>Servicios básicos</h4>
            <div id="automatic_buttons" class="container-fluid btn-group-vertical mb-5">
            </div>
        </div>
    <?php foreach ( $this->custom_page_list as $page_data ) :
        $id = $page_data['id'];
        ?>
        <div class="container-md" data-page-name="<?=$id?>_page" style="display:none">
        <?=$this->getSectionContent($id)?>
        </div>
    <?php endforeach; ?>
    <?php foreach ( $this->custom_method_form_list as $form_data ) :
        $method = $form_data['method'];
        $id = $form_data['id'];
        $endpoint = $form_data['endpoint'];
        ?>
        <div class="container-md" data-page-name="<?=$id?>_page" style="display:none">
            <nav class="navbar navbar-light bg-light">
                <span class="navbar-brand"><?=$method?></span>
                <button class="btn btn-outline-secondary btn-sm" onclick="changePage('index_page')">Volver</button>
            </nav>
            <form id="<?=$id?>_form">
                <?=$this->getSectionContent($id)?>
                <button type="submit" class="btn btn-primary" onclick="submitRequest('<?=$endpoint?>', '<?=$id?>_form' ,'<?=$method?>')">Enviar</button>
            </form>
            <nav class="navbar navbar-light bg-light pt-5">
              <span class="navbar-brand">Resultado</span>
              <button class="btn btn-outline-secondary btn-sm"  onclick="newWindow('<?=$method?>')">New Window</button>
            </nav>
            <div class="embed-responsive embed-responsive-21by9">
              <iframe class="embed-responsive-item" id="<?=$method?>_target"></iframe>
            </div>
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
        html += "<div class=\"container-md\" data-page-name=\"" + automatic_method.method + "_page\" style=\"display:none\">" +
                "   <nav class=\"navbar navbar-light bg-light\">\n" +
                "       <span class=\"navbar-brand\">" + automatic_method.method +"</span>" +
                "       <button class=\"btn btn-outline-secondary btn-sm\" onclick=\"changePage('index_page')\">Volver</button>" +
                "    </nav>" +
                "    <form id=\"" + automatic_method.method +"_form\">";

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
            "   <nav class=\"navbar navbar-light bg-light pt-5\">\n" +
            "       <span class=\"navbar-brand\">Resultado</span>" +
            "       <button class=\"btn btn-outline-secondary btn-sm\"  onclick=\"newWindow('" + automatic_method.method + "')\">New Window</button>" +
            "    </nav>" +
            "    <div class=\"embed-responsive embed-responsive-21by9\">" +
            "      <iframe class=\"embed-responsive-item\" id=\"" + automatic_method.method + "_target\"></iframe>" +
            "    </div>" +
        "</div>";
    }

    let html_automatic_buttons = "";
    for ( let automatic_method of myJson )
        if ( automatic_method.parameter_list.length > 0 ) {
            html_automatic_buttons += "        <button  type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"changePage('" + automatic_method.method + "_page')\">" + automatic_method.method + "</button>";
        } else {
            html_automatic_buttons += "        <button  type=\"button\" class=\"btn btn-sm btn-outline-secondary\" onclick=\"submitRequestGet('" + automatic_method.endpoint + "', 'method=" + automatic_method.method + "')\">" + automatic_method.method + "</button>";
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