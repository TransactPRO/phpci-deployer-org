<?php

// All TransactPRO/phpci-deployer-org recipes are based on `common.deploy.php` (see in examples https://github.com/TransactPRO/phpci-deployer-org/tree/master/examples).
require __DIR__ . '/common.deploy.php';

$path_to_projects     = '/path/to/projects';
$project              = 'example';

$deploy_path = $path_to_projects.'/'.$project;

server('prod', 'production.example.com', 22)
    ->user('deploy')
    ->identityFile('~/.ssh/id_rsa.pub', '~/.ssh/id_rsa', 'your-id-rsa-password')
    ->stage('prod')
    ->env('deploy_path', $deploy_path);

set('repository', 'https://YOUR_STASH_USERNAME:YOUR_STASH_PASSWORD@git.example.com:YOUR_STASH_PORT/scm/xyz/example.git');
set('http_user', 'apache'); // define your webserver user (apache|nginx)

/**
 * Custom task
 */
task('deploy:custom_task', function () {
    run("echo 'This is custom task output'");
})->desc('Running a custom task');

/**
 * Main task
 */
task('deploy', [
    'deploy:prepare',
    'deploy:release',
    'deploy:update_code',
    'deploy:vendors',
    'deploy:custom_task',
    'deploy:shared',
    'deploy:symlink',
    'deploy:writable',
    'deploy:shared_writable',
    'cleanup',
    // 'deploy:notify',
])->desc('Deploy your project');
after('deploy', 'success');
