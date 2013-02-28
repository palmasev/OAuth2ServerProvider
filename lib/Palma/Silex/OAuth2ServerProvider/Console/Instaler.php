<?php

namespace Palma\Silex\OAuth2ServerProvider\Console;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Instaler extends Command
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
		$option_connection = array('driver' => $driver);

		$user = $dialog->ask(
			$output,
			'<info>user of database connection: </info>'
		);

		if ($user!='') {
			$option_connection['user'] = $user;
		}

		if ($user!='') {
			$password = $dialog->ask(
				$output,
				'<info>password of database connection: </info>'
			);
			if ($password!='') {
				$option_connection['password'] = $password;
			}

		}

		if ($driver == 'pdo_sqlite') {
			$memory = $dialog->askConfirmation(
		        $output,
		        '<info>SQlite database in memory?</info>',
		        false
		    );
			if($memory) {
				$option_connection['memory'] = $memory;
			} else {
				$path = $dialog->ask(
					$output,
					'<info>Database path [./]</info>',
					'./'
				);
				$option_connection['path'] = $path; 
			}
		}

    }
	
}