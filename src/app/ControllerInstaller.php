<?php
declare(strict_types=1);

namespace labo86\rdtas\app;


abstract class ControllerInstaller
{
    private Config $config;

    public function __construct(Config $config) {
        $this->config = $config;
    }

    public function getConfig() : Config {
        return $this->config;
    }

    abstract function prepareDataStores();

    public function prepareDataAccessFolder(DataAccessFolder $dao) {
        $dao->createDirectory();
        $folder = $dao->getConfig()->getFolder();
        $www_user = $this->getConfig()->getUserWww();

        if ( !is_null($www_user) )
            passthru(sprintf('chown -R %s:%s %s', $www_user, $www_user, $folder));
    }

    public function prepareDataAccessDb(DataAccessDb $dao) {
        $dao->createTables();
        $type = $dao->getConfig()->getType();
        if ( $type === DataAccessDb::TYPE_SQLITE ) {
            $www_user = $this->getConfig()->getUserWww();

            if ( !is_null($www_user) )
                passthru(sprintf('chown -R %s:%s %s', $www_user, $www_user, $dao->getConfig()->getName()));

        }

    }
}