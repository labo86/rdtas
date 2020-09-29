<?php
declare(strict_types=1);

namespace labo86\rdtas\app;


class GenericData
{
    const GENERIC_ENTITY_TABLE_DDL = <<<EOF
create table generic_entities
(
    entity_id varchar(36) no null,
    type varchar(36) null,
    user_id varchar(36),
    parent_entity_id varchar(36) null,
	name varchar(36) null,
	data text null
);
EOF;

    const GENERIC_RELATION_TABLE_DDL = <<<EOF
create table generic_relations
(
	a_entity_id varchar(36) not null
	b_entity_id varchar(36) not null,
    user_id varchar(36),
	type varchar(36) null,
	name varchar(36) null,
	data text null
);
EOF;
}

