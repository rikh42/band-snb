<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;
use \snb\http\Request;
use snb\routing\Route;

interface KernelInterface
{
    public function boot();
    public function shutdown();
    public function getPackagePath($name);
    public function findPath($name);
    public function findResource($name, $type);
    public function handle(Request $request);
    public function forwardToRoute(Route $route);

	public function isDebug();

	public function getEnvironment();
	public function getSession();
	public function getRequest();
	public function getConfigValue($name, $default = null);
	public function getRouteData();

    /**
     * @param string $name - the name of the service to add
     * @param $ref - it's classname or an instance of the service
     * @return ServiceDefinition
     */
    public function addService($name, $ref);
	public function addServiceAlias($name, $alias);
    public function addModel($name, $ref);
    public function getContainer();
}
