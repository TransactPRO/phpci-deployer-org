<?php
/**
 * Deployer plugin for PHPCI
 * @see http://deployer.org
 *
 * @copyright
 * @license MIT
 * @license https://github.com/ket4yii/phpci-deployer-plugin/blob/master/LICENSE
 * @link https://github.com/ket4yii/phpci-deployer-plugin
 */

namespace PHPCI\Plugin;

use PHPCI\Builder;
use PHPCI\Model\Build;

class Deployer implements \PHPCI\Plugin {

  protected $phpci;
  protected $build;
  protected $config;
  protected $dep;
  protected $branch;

  /**
   * Standard Constructor
   *
   * $options['directory'] Output Directory. Default: %BUILDPATH%
   * $options['filename']  Phar Filename. Default: build.phar
   * $options['regexp']    Regular Expression Filename Capture. Default: /\.php$/
   * $options['stub']      Stub Content. No Default Value
   *
   * @param Builder $phpci   PHPCI instance
   * @param Build   $build   Build instance
   * @param array   $options Plugin options
   */
  public function __construct(
      Builder $phpci,
      Build $build,
      array $options = array()
  ) {
    $this->phpci = $phpci;
    $this->build = $build;
    $this->config = $options;

    $this->dep = $this->phpci->findBinary('dep');
    $this->branch = $this->build->getBranch();
  }

  /**
   * PHPCI plugin executor.
   *
   * @return bool Did plugin execute successfully
   */
  public function execute() {
    $task = 'deploy'; //default task is deploy
    $verbosity = ''; //default verbosity is normal
    $filename = '';

    if (($validationResult = $this->validateConfig()) !== NULL) {
      $this->phpci->log($validationResult['message']);

      return $validationResult['successful'];
    }
    
    if(isset($this->config[$this->branch])){
        $branchConfig = $this->config[$this->branch];
    }else{
        $branchConfig = $this->config['*'];
    }

    $stage = $branchConfig['stage'];

    if (!empty($branchConfig['task'])) {
      $task = $branchConfig['task'];
    }

    if (!empty($branchConfig['verbose'])) {
      $verbosity = $this->getVerbosityOption($branchConfig['verbose']);
    }

    if (!empty($branchConfig['file'])) {
      $file = '--file=' . $branchConfig['file'];
    }

    $deployerCmd = "$this->dep $file $verbosity $task $stage --commit={$this->build->getCommitId()}";

    return $this->phpci->executeCommand($deployerCmd);
  }

  /**
   * Validate config.
   *
   * $validationRes['message'] Message to log
   * $validationRes['successful'] Plugin status that is connected with error
   *
   *  @return array validation result
   */
  protected function validateConfig() {
    if (empty($this->config)) {
      return [
        'message' => 'Can\'t find configuration for plugin!',
        'successful' => false
      ];
    }

    if (empty($this->config[$this->branch])) {
      return [
        'message' => 'There is no specified config for this branch.',
        'successful' => true
      ];
    }

    $branchConf = $this->config[$this->branch];

    if (empty($branchConf['stage'])) {
      return [
        'message' => 'There is no stage for this branch',
        'successful' => false
      ];
    }

    return null;
  }

  /**
   * Get verbosity flag.
   *
   * @param string $verbosity User defined verbosity level
   *
   * @return string Verbosity flag
   */
  protected function getVerbosityOption($verbosity) {
    $LOG_LEVEL_ENUM = [
      'verbose' =>'v',
      'very verbose' => 'vv',
      'debug' => 'vvv',
      'quiet' => 'q'
    ];

    $verbosity = strtolower(trim($verbosity));
    return '-vvv';
    if ($verbosity !== 'normal') {
      return '-' . $LOG_LEVEL_ENUM[$verbosity];
    } else {
      return '';
    }

  }
}
