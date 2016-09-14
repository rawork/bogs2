<?php

namespace Fuga\Component\Install;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class FixCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('install:fix')
            ->setDescription('Fix rights for folders')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->chmod_R(__DIR__.'/../../../../app/logs/', 666, 777, $output);  
		chmod(__DIR__.'/../../../../app/logs/.gitkeep', 0644);
		$this->chmod_R(__DIR__.'/../../../../app/cache/', 666, 777, $output);  
		chmod(__DIR__.'/../../../../app/cache/.gitkeep', 0644);
		chmod(__DIR__.'/../../../../app/cache/smarty/.gitkeep', 0644);
		chmod(__DIR__.'/../../../../app/cache/twig/.gitkeep', 0644);
		chmod(__DIR__.'/../../../../app/cache/proxies/.gitkeep', 0644);
		chmod(__DIR__.'/../../../../app/cache/hydrators/.gitkeep', 0644);
		$this->chmod_R(__DIR__.'/../../../../files/', 666, 777, $output);  
		chmod(__DIR__.'/../../../../files/.htaccess', 0644);
		$this->chmod_R(__DIR__.'/../../../../thumbs/', 666, 777, $output);  
		chmod(__DIR__.'/../../../../thumbs/.htaccess', 0644);
		$this->chmod_R(__DIR__.'/../../../../upload/', 666, 777, $output);
		chmod(__DIR__.'/../../../../upload/.htaccess', 0644);
		$this->chmod_R(__DIR__.'/../../../../app/backup/', 666, 777, $output);
		chmod(__DIR__.'/../../../../app/backup/.gitkeep', 0644);
				
		$output->writeln('<info>Paths are fixed</info>');
    }
	
	protected function chmod_R($path, $filemode, $dirmode, $output) { 
		if (is_dir($path) ) { 
			if (!chmod($path, octdec($dirmode))) { 
				$dirmode_str= $dirmode; 
				$output->writeln("<error>Failed applying filemode '$dirmode_str' on directory '$path'</error>"); 
				$output->writeln("<error> `-> the directory '$path' will be skipped from recursive chmod</error>"); 
				return; 
			} 
			$dh = opendir($path); 
			while (($file = readdir($dh)) !== false) { 
				if($file != '.' && $file != '..') {  // skip self and parent pointing directories 
					$fullpath = $path.'/'.$file; 
					$this->chmod_R($fullpath, $filemode, $dirmode, $output); 
				} 
			} 
			closedir($dh); 
		} else { 
			if (is_link($path)) { 
				return; 
			} 
			if (!chmod($path, octdec($filemode))) { 
				$filemode_str = $filemode; 
				$output->writeln("<error>Failed applying filemode '$filemode_str' on file '$path'</error>"); 
				return; 
			} 
		} 
	} 
}