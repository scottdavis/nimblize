<?php
$exclude = array('.git', 'test', 'scripts', 'profile', 'phpunit.xml', '.DS_Store', 'package.xml');
$path = __DIR__;
$reg_path = str_replace('/', '\/', $path);
$reg = '/^(' . implode('|', $exclude) .')/';
$reg = str_replace('.', '\.', $reg);
var_dump($reg);
$spec = PackageSpec::create(array(PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('nimblize')
            ->setChannel('pear.nimblize.com')
            ->setSummary('Nimblize php framework')
            ->setDescription('Php Framework and ORM')
            ->setReleaseVersion('0.0.1')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.1')
            ->setApiStability('alpha')
            ->setLicense(PackageSpec::LICENSE_MIT)
            ->setNotes('Initial release.')
            ->addMaintainer('lead', 'Scott Davis', 'jetviper21', 'jetviper21@gmail.com')
            ->addGitFiles()
            ->addExecutable('nimble_scripts/nimblize')
						->addExecutable('nimble_scripts/nimblize.bat')
						->addExcludeFilesRegex($reg)
            ;