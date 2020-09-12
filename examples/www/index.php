<?php

include_once(__DIR__ . '/../../vendor/autoload.php');

use labo86\rdtas\staty\BlockAutoServices;
use labo86\staty\Block;

$block = new BlockAutoServices(Block::thisPage());
$block->setService('service');
$block->sectionBeginForm('something');?>
something
<?php
$block->sectionEnd();
$block->html();


