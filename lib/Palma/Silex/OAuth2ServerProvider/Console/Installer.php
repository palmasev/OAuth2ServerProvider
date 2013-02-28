<?php

namespace Palma\Silex\OAuth2ServerProvider\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Palma\Silex\OAuth2ServerProvider\Generator\DatabaseGenerator;

class Installer extends Command
{
	protected function configure()
	{
		$this
			->setName('oauth2server:install')
			->setDescription('Run database script');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');
        $driver = $dialog->askAndValidate(
		    $output,
		    '<info>Database driver ([pdo_mysql] | pdo_sqlite | pdo_pgsql | pdo_oci | pdo_sqlsrv | oci8): </info>',
		    function ($answer) {
		    	if (($answer!='') && !in_array($answer, array("pdo_mysql", "pdo_sqlite", "pdo_pgsql", "pdo_oci", "pdo_sqlsrv", "oci8"))) {
		    		throw new \RunTimeException(
		                'Only pdo_mysql, pdo_sqlite, pdo_pgsql, pdo_oci, pdo_sqlsrv or oci8 drivers are available'
		            );
		    	}
		    	return $answer;
		    },
		    3,
		    'pdo_mysql'
		);
        $output->writeln($driver);
		$connectionParams = array('driver' => $driver);

		$user = $dialog->ask(
			$output,
			'<info>user of database connection: </info>'
		);

		if ($user!='') {
			$connectionParams['user'] = $user;
		}

		if ($user!='') {
			$password = $dialog->ask(
				$output,
				'<info>password of database connection: </info>'
			);
			if ($password!='') {
				$connectionParams['password'] = $password;
			}

		}

		if ($driver == 'pdo_sqlite') {
			$memory = $dialog->askConfirmation(
		        $output,
		        '<info>SQlite database in memory?</info>',
		        false
		    );
			if($memory) {
				$connectionParams['memory'] = $memory;
			} else {
				$path = $dialog->ask(
					$output,
					'<info>Database path [./]</info>',
					'./'
				);
				$connectionParams['path'] = $path; 
			}
		} else {
			$host = $dialog->ask(
			    $output,
			    '<info>Database host [localhost]: </info>',
			    'localhost'
			);
			$connectionParams['host'] = $host;

			$port = $dialog->ask(
			    $output,
			    '<info>Database port: </info>'
			);
			if ($port != '') {
				$connectionParams['port'] = $port;
			}

			$dbname = $dialog->ask(
			    $output,
			    '<info>Database name: </info>',
			    'oauth2'
			);
			$connectionParams['dbname'] = $dbname;

			if ($driver == 'pdo_mysql') {
				$socket = $dialog->ask(
				    $output,
				    '<info>Name of the socket used to connect to the database: </info>'
				);
				if ($socket != '') {
					$connectionParams['socket'] = $socket;
				}
			}

			if($driver != 'pdo_pgsql' && $driver != 'pdo_sqlsrv') {
				$charset = $dialog->ask(
				    $output,
				    '<info>Charset: </info>'
				);
				if ($charset != '') {
					$connectionParams['charset'] = $charset;
				}
			}
		}

		//TODO create database connection
		$dg = new DatabaseGenerator($connectionParams);
		$output->writeln('Listado de bases de datos');
		foreach ($dg->preRun() as $db) {
			$output->writeln($db);
		}
		
		$sure = $dialog->askConfirmation(
	        $output,
	        '<info>Are you sure? (y/[n])</info>',
	        false
	    );

	    if ($sure) {
	    	$dg->run();
	    }
    }
	
}