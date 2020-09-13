<?php
declare(strict_types=1);

include_once(__DIR__ . '/../../vendor/autoload.php');

use labo86\rdtas\staty\BlockPageEasyServices;
use labo86\staty\Block;

$BLOCK = new BlockPageEasyServices(Block::thisPage());
$BLOCK->setService('services/services.json');
$BLOCK->sectionBeginForm('something');?>
something
<?php
$BLOCK->html();


