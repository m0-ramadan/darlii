<?php

namespace App\Routing;

use Illuminate\Routing\UrlGenerator;

class CustomUrlGenerator extends UrlGenerator
{
    /**
     * Get the URL to a named route, omitting 'locale' parameter
     * if the route does not explicitly define {locale}.
     *
     * @param  \Illuminate\Routing\Route|string  $route
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    public function toRoute($route, $parameters = [], $absolute = false)
    {
        $routeInstance = is_string($route) ? $this->routes->getByName($route) : $route;

        if ($routeInstance && is_array($parameters) && isset($parameters['locale'])) {
            if (! in_array('locale', $routeInstance->parameterNames(), true)) {
                unset($parameters['locale']);
            }
        }

        return parent::toRoute($route, $parameters, $absolute);
    }

    /**
     * Generate an absolute URL to the given path, stripping leading ar locale.
     *
     * @param  string  $path
     * @param  mixed  $extra
     * @param  bool|null  $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        if (is_string($path)) {
            $path = preg_replace('#^/?ar(?:/|$)#i', '/', $path) ?? $path;
        }

        return parent::to($path, $extra, $secure);
    }
}
