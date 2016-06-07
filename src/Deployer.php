<?php
/**
 * PHPCI - Continuous Integration for PHP
 *
 * @copyright    Copyright 2014, Block 8 Limited.
 * @license      https://github.com/Block8/PHPCI/blob/master/LICENSE.md
 * @link         https://www.phptesting.org/
 */

namespace PHPCI\Plugin;

use PHPCI\Builder;
use PHPCI\Helper\Lang;
use PHPCI\Model\Build;
use b8\Config;

/**
 * StashBuild Plugin
 * @author       Vitalijs Litvinovs <vl@tpro.lv>
 * @package      PHPCI
 * @subpackage   Plugins
 */
class StashBuild implements \PHPCI\Plugin
{
    private $authUser = null;
    private $authToken = null;
    private $login = null;
    private $password = null;
    protected $color;
    protected $notify;

    /**
     * Set up the plugin, configure options, etc.
     * @param Builder $phpci
     * @param Build $build
     * @param array $options
     * @throws \Exception
     */
    public function __construct(Builder $phpci, Build $build, array $options = array())
    {
        $this->phpci = $phpci;
        $this->build = $build;

        $pluginConfig = $this->phpci->getSystemConfig('stash_build');

        $this->userAgent = "PHPCI/1.0 (+http://www.phptesting.org/)";
        $this->cookie = "phpcicookie";

        $buildSettings = $phpci->getConfig('build_settings');

        if (isset($pluginConfig['auth_token'], $pluginConfig['auth_user'])) {
            $this->authUser = $pluginConfig['auth_user'];
            $this->authToken = $pluginConfig['auth_token'];
        } else {
            $this->login = $pluginConfig['login'];
            $this->password = $pluginConfig['password'];
        }

        if (!empty($options['status'])) {
            $this->status = $options['status'];
        }


        $this->url = $pluginConfig['url'] . '/rest/build-status/1.0/commits';
    }

    /**
     * Run the StashBuild plugin.
     * @return bool
     */
    public function execute()
    {
        $url = $this->url . "/%COMMIT%";
        $buildStatus = json_encode([
            'state'       => $this->status,
            'key'         => '%BRANCH%-%BUILD%',
            'name'        => "%BRANCH% #%BUILD%",
            'url'         => '%BUILD_URI%',
            'description' => "PHPCI Build #%BUILD% for commit %SHORT_COMMIT%"
        ]);

        if (null === $this->authToken) {
            $authHeaders = $this->buildParams(['u' => [
                $this->login => $this->password
            ], 'H' => ["Content-Type" => "application/json"]]);
        } else {
            $authHeaders = $this->buildParams(['H' => [
                'X-Auth-User'  => $this->authUser,
                'X-Auth-Token' => $this->authToken,
		'Content-Type' => "application/json"
            ]]);
        }

        $result = $this->makeCurlPost($url, $authHeaders, $buildStatus);

        return $result;
    }

    private function buildParams($params)
    {
        $paramString = '';
        foreach($params as $type => $array){
            foreach($array as $k => $v){
                $paramString .= ' -' . $type . ' "' . $k . ':' . $v . '" ';
            }
        }
        return $paramString;
    }

    private function makeCurlPost($url, $params, $data){
        return $this->phpci->executeCommand(
            $this->phpci->interpolate(
                "curl -s " . $params 
                . " -X POST " . $url 
                . " -d '" . addslashes($data) . "'"
            )
        );
    }
}
