<?php
$exclude = array('.git', 'test', 'scripts', 'profile', 'phpunit.xml', '.DS_Store', 'package.xml');
$path = __DIR__;
$reg_path = str_replace('/', '\/', $path);
$reg = '/^(' . implode('|', $exclude) .')/';
$reg = str_replace('.', '\.', $reg);
$spec = Pearfarm_PackageSpec::create(array(Pearfarm_PackageSpec::OPT_BASEDIR => dirname(__FILE__)))
            ->setName('nimblize')
            ->setChannel('jetviper21.pearfarm.org')
            ->setSummary('Nimblize php framework')
            ->setDescription('Php Framework and ORM')
            ->setReleaseVersion('0.0.3')
            ->setReleaseStability('alpha')
            ->setApiVersion('0.0.3')
            ->setApiStability('alpha')
            ->setLicense(Pearfarm_PackageSpec::LICENSE_MIT)
            ->setNotes('New serializer added.')
            ->addMaintainer('lead', 'Scott Davis', 'jetviper21', 'jetviper21@gmail.com')
            ->addMaintainer('lead', 'John Bintz', 'johnbintz', 'john@coswellproductions.com')
            ->addGitFiles()
            ->addExecutable('nimble_scripts/nimblize')
						->addExecutable('nimble_scripts/nimblize.bat')
						->addExcludeFilesRegex($reg)
            ;