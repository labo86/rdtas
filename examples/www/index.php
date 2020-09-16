<?php
declare(strict_types=1);

include_once(__DIR__ . '/../../vendor/autoload.php');

use labo86\rdtas\staty\BlockPageEasyServices;
use labo86\staty\Block;
$page = Block::thisPage();
$page->prepareMetadata([
   'title' => 'Servicios automáticos',
   'description' => 'Alguna descripción lorem ipsum'
]);

$BLOCK = new BlockPageEasyServices($page);
$BLOCK->setService('services/services.json');
$BLOCK->sectionBeginForm('something', 'services/services.json');?>
Algo 1
<?php $BLOCK->sectionBeginForm('something', 'services/services.json');?>
Algo 2
<?php
$BLOCK->html();


