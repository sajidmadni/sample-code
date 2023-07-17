<?php

/*
 * This file is part of the Doctrine Bundle
 *
 * The code was originally distributed inside the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 * (c) Doctrine Project, Benjamin Eberlei <kontakt@beberlei.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Navio\HospitalBundle\Factory;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;

/**
 * Connection
 * http://stackoverflow.com/questions/20805637/symfony2-dynamic-doctrine-database-connections-at-runtime
 */
class CustomConnectionFactory extends ConnectionFactory 
{
    private $typesConfig = array();
    private $commentedTypes = array();
    private $initialized = false;

    /**
     * Construct.
     *
     * @param array $typesConfig
     */
    public function __construct(array $typesConfig)
    {
        $this->typesConfig = $typesConfig;
    }

    /**
     * Create a connection by name.
     *
     * @param array         $params
     * @param Configuration $config
     * @param EventManager  $eventManager
     * @param array         $mappingTypes
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function createConnection(array $params, Configuration $config = null, EventManager $eventManager = null, array $mappingTypes = array())
    {       
        global $kernel;// //TODO  Can I get this off the container?
	$hostFile = $kernel->getContainer()->getParameter('database_host_file');

        $host=$params['host'];
        if(file_exists($hostFile)){
            $host = file_get_contents($hostFile);
        }
        $params['host']=trim($host);

        //var_dump($host);var_dump($params); var_dump($allowedArray); exit;
        //continue with regular connection creation using new params
        return parent::createConnection($params, $config, $eventManager,$mappingTypes);
    }
}
