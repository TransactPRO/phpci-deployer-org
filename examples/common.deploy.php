<?php

// cat ~/.ssh/id_rsa.pub | ssh user@hostname 'cat >> .ssh/authorized_keys'

// All Deployer recipes are based on `recipe/common.php`.
require 'recipe/common.php';

// These two lines with linked functions were project/env specific, but let it stay here just for example purposes
set('shared_writable_dirs', []);
set('shared_writable_files', []);

// If project has its own deploy.php -> include it (see manual of TransactPRO/phpci-deployer-org to learn more)
if(file_exists('deploy.php')){
    include_once 'deploy.php';
}

option('branch', null, \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL, 'Branch to deploy.', 'master');

/**
 * There should be simple way to get tag option. Leaving this as is just due to insufficient knowledge/tools.
 */
function _getOption($option, $default = ''){
    global $argv;
    $optionValue = $default;
    foreach($argv as $arg){
        if(preg_match('/^--'.$option.'=/i', $arg)){
            $optionValue = preg_replace('/--'.$option.'="?(.*)"?/i', "$1", $arg);
            break;
        }
    }
    return $optionValue;
}
$branch = _getOption('branch', 'master');

set('writable_use_sudo', false);

/**
 * Create writable symlinks for shared directories and files.
 */
task('deploy:shared_writable', function () {
    $sudo = get('writable_use_sudo') ? 'sudo' : '';
    $sharedPath = "{{deploy_path}}/shared";
    foreach (get('shared_writable_dirs') as $dir) {
        // Remove from source.
        run("if [ -d $(echo {{release_path}}/$dir) ]; then rm -rf {{release_path}}/$dir; fi");
        // Create shared dir if it does not exist.
        run("mkdir -p $sharedPath/$dir");
        // Make it writable
        run("$sudo chmod 777 -R $sharedPath/$dir");
        // Create path to shared dir in release dir if it does not exist.
        // (symlink will not create the path and will fail otherwise)
        run("mkdir -p `dirname {{release_path}}/$dir`");
        // Symlink shared dir to release dir
        run("ln -nfs $sharedPath/$dir {{release_path}}/$dir");
    }
    foreach (get('shared_writable_files') as $file) {
        $dirname = dirname($file);
        // Remove from source.
        run("if [ -f $(echo {{release_path}}/$file) ]; then rm -rf {{release_path}}/$file; fi");
        // Ensure dir is available in release
        run("if [ ! -d $(echo {{release_path}}/$dirname) ]; then mkdir -p {{release_path}}/$dirname;fi");
        // Create dir of shared file
        run("mkdir -p $sharedPath/" . $dirname);
        // Touch shared
        run("touch $sharedPath/$file");
        // Make it writable
        run("$sudo chmod 777 -R $sharedPath/$file");
        // Symlink shared dir to release dir
        run("ln -nfs $sharedPath/$file {{release_path}}/$file");
    }
})->desc('Creating writable symlinks for shared files');
