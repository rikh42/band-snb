<?php
/**
 * This file is part of the Small Neat Box Framework
 * Copyright (c) 2011-2012 Small Neat Box Ltd.
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace snb\core;

use snb\core\ContainerAware;
use snb\http\Response;
use snb\http\Request;

/**
 * A base class for Controllers that gives you a few handy helpers
 */
class Controller extends ContainerAware
{

    /**
     * Called once the object has been created and features added.
     * Allows sub-classes to hook into this process and add in any features they need access to
     * @return bool
     */
    public function init()
    {
        return true;
    }



    /**
     * @param  string $name
     * @param  array  $data
     * @return string
     */
    public function render($name, array $data = array())
    {
        return $this->getView()->render($name, $data);
    }

    /**
     * Render a view and store the result in a response object (created if needed)
     *
     * @param string                  $name
     * @param array                   $data
     * @param null|\snb\http\Response $response
     *
     * @return \snb\http\Response
     */
    public function renderResponse($name, array $data=array(), Response $response=null)
    {
        // create a response, if one wasn't provided
        if ($response==null) {
            $response = new Response();
        }

        // render the view into the response and return it
        $response->setContent($this->render($name, $data));

        return $response;
    }


    /**
     * Made it simpler to respond with json from a controller
     * @param $data
     * @param \snb\http\Response $response
     * @return \snb\http\Response
     */
    public function jsonResponse($data, Response $response=null)
    {
        // create a response, if one wasn't provided
        if ($response==null) {
            $response = new Response();
        }

        // render the view into the response and return it
        $response->setContentJson($data);
        $response->setContentTypeSimple('json');
        return $response;
    }



    /**
     * Creates or updates a response to be a redirect to the named route
     * @param  string                  $routeName
     * @param  array                   $args
     * @param  null|\snb\http\Response $response
     * @return null|\snb\http\Response
     */
    public function redirectResponse($routeName, array $args=array(), Response $response = null)
    {
        // create a response, if one wasn't provided
        if ($response == null) {
            $response = new Response();
        }

        // Find the route mentioned
        $routeCollection = $this->getRoutes();
        if ($routeCollection) {
            $route = $routeCollection->find($routeName);
            if ($route) {
                $response->setRedirectToRoute($route, $args, $this->getRequest());
            }
        }

        // return it.
        return $response;
    }


    /**
     * Redirect to a specific URL (not a named route)
     * Ideally should be a fully qualified URl (http://blar.com/etc.htm), but a relative URL
     * is also OK (/etc.htm) as we add the current requests http host to it first.
     * @param $url
     * @param \snb\http\Response $response
     * @return \snb\http\Response
     */
    public function redirectUrlResponse($url, Response $response = null)
    {
        // create a response, if one wasn't provided
        if ($response == null) {
            $response = new Response();
        }

        // If the URL appears to be a relative URL (/some/path/test.png)
        if (preg_match('%^/[^/]%iu', $url) == 1)
        {
            // Add in the protocol and host
            /* @var $request \snb\http\Request */
            $request = $this->container->get('request');
            if ($request) {
                $url = $request->getHttpHost() . $url;
            }
        }

        // finally, set the redirection
        $response->setRedirectToURL($url);
        return $response;
    }



    /**
     * Get the URL for the named route.
     * @param $routeName
     * @param array $args
     * @param bool $fullyQualified
     * @return string
     */
    public function getUrlForRoute($routeName, $args=array(), $fullyQualified=false)
    {
        // Find the route mentioned
        $routes = $this->getRoutes();
        return $routes->generate($routeName, $args, $fullyQualified);
    }



    /**
     * @return \snb\view\ViewInterface
     */
    public function getView()
    {
        return $this->container->get('view');
    }

    /**
     * @return \snb\core\DatabaseInterface
     */
    public function getDatabase()
    {
        return $this->container->get('database');
    }

    /**
     * @return \snb\http\Request
     */
    public function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return \snb\logger\LoggerInterface
     */
    public function getLogger()
    {
        return $this->container->get('logger');
    }

    /**
     * @return \snb\routing\RouteCollection
     */
    public function getRoutes()
    {
        return $this->container->get('routes');
    }

    /**
     * @return \snb\form\FormBuilder
     */
    public function getFormBuilder()
    {
        return $this->container->get('form.builder');
    }

    /**
     * @return \snb\security\Auth
     */
    public function getAuth()
    {
        return $this->container->get('auth');
    }

    /**
     * @return \snb\cache\CacheInterface
     */
    public function getCache()
    {
        return $this->container->get('cache');
    }

    /**
     * get
     * Allows services to be accessed without having to use the container
     * eg, use $this->get('database'), instead of $this->container->get('database');
     * @param  string $name
     * @return mixed
     */
    public function get($name)
    {
        return $this->container->get($name);
    }
}
