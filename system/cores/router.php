<?php
/**
 * Igniter Router
 *
 * This it the Igniter URL Router, the layer of a web application between the
 * URL and the function executed to perform a request. The router determines
 * which function to execute for a given URL.
 *
 * @package Cores
 * @subpackage Router
 * @version 2.0.1
 * @author Brandon Wamboldt <brandon.wamboldt@gmail.com>
 */
// Using the Igniter namespace, you can access the router using \Igniter\Router
//namespace Igniter;

/**
 * 路由器class
 *
 * Igniter Router Class
 *
 * This it the Igniter URL Router, the layer of a web application between the
 * URL and the function executed to perform a request. The router determines
 * which function to execute for a given URL.
 *
 * <code>
 * $router = new \Igniter\Router;
 *
 * // Adding a basic route
 * $router->route( '/login', 'login_function' );
 *
 * // Adding a route with a named alphanumeric capture, using the <:var_name> syntax
 * $router->route( '/user/view/<:username>', 'view_username' );
 *
 * // Adding a route with a named numeric capture, using the <#var_name> syntax
 * $router->route( '/user/view/<#user_id>', array( 'UserClass', 'view_user' ) );
 *
 * // Adding a route with a wildcard capture (Including directory separtors), using
 * // the <*var_name> syntax
 * $router->route( '/browse/<*categories>', 'category_browse' );
 *
 * // Adding a wildcard capture (Excludes directory separators), using the
 * // <!var_name> syntax
 * $router->route( '/browse/<!category>', 'browse_category' );
 *
 * // Adding a custom regex capture using the <:var_name|regex> syntax
 * $router->route( '/lookup/zipcode/<:zipcode|[0-9]{5}>', 'zipcode_func' );
 *
 * // Specifying priorities
 * $router->route( '/users/all', 'view_users', 1 ); // Executes first
 * $router->route( '/users/<:status>', 'view_users_by_status', 100 ); // Executes after
 *
 * // Specifying a default callback function if no other route is matched
 * $router->default_route( 'page_404' );
 *
 * // Run the router
 * $router->execute();
 * </code>
 *
 * @since 2.0.0
 */
class Router {
    /**
     * Contains the callback function to execute, retrieved during run()
     *
     * @var String|Array The callback function to execute during dispatch()
     * @since 2.0.1
     * @access protected
     */
    protected $callback = NULL;
    /**
     * Contains the callback function to execute if none of the given routes can
     * be matched to the current URL.
     *
     * @var String|Array The callback function to execute as a fallback option
     * @since 2.0.0
     * @access protected
     */
    protected $default_route = NULL;
    /**
     * An array containing the parameters to pass to the callback function,
     * retrieved during run()
     *
     * @var Array An array containing the list of routing rules
     * @since 2.0.1
     * @access protected
     */
    public static $params = array();
    /**
     * An array containing the list of routing rules and their callback
     * functions, as well as their priority and any additional paramters.
     *
     * @var Array An array containing the list of routing rules
     * @since 2.0.0
     * @access protected
     */
    protected $routes = array();
    /**
     * A sanitized version of the URL, excluding the domain and base component
     *
     * @var String A clean URL
     * @since 2.0.0
     * @access protected
     */
    protected $url_clean = '';
    /**
     * The dirty URL, direct from $_SERVER['REQUEST_URI']
     *
     * @var String The unsanitized URL (Full URL)
     * @since 2.0.0
     * @access protected
     */
    protected $url_dirty = '';
    /**
     * The controller name
     *
     * @since 2.0.0
     * @access protected
     */
    public static $controller = '';
    /**
     * The action for controller
     *
     * @since 2.0.0
     * @access protected
     */
    public static $action = '';
    /**
     * The module for controller
     *
     * @since 2.0.0
     * @access protected
     */
    public static $module = 'default';
    /**
     * Initializes the router by getting the URL and cleaning it
     * @param $url url
     * @since 2.0.0
     * @access protected
     */
    public function __construct($url = NULL) {
        if ($url == NULL) {
            // Get the current URL, differents depending on platform/server software
            if (isset($_SERVER['REQUEST_URL']) && !empty($_SERVER['REQUEST_URL'])) {
                $url = $_SERVER['REQUEST_URL'];
            }
            else {
                $url = $_SERVER['REQUEST_URI'];
            }
        }
        // Store the dirty version of the URL
        $this->url_dirty = $url;
        // Clean the URL, removing the protocol, domain, and base directory if there is one
        $this->url_clean = $this->__get_clean_url($this->url_dirty);
    }
    /**
     * If the router cannot match the current URL to any of the given routes,
     * the function passed to this method will be executed instead. This would
     * be useful for displaying a 404 page for example.
     *
     * @since 2.0.0
     * @access public
     *
     * @param string|array $callback The function or class + function to execute if no other routes are matched
     */
    public function default_route($callback) {
        $this->default_route = $callback;
        return $this;
    }
    /**
     * Tries to match one of the URL routes to the current URL, otherwise
     * execute the default function and return false.
     *
     * @since 2.0.1
     * @access public
     *
     * @return bool True if a route was matched, false if not
     */
    public function run() {
        // Whether or not we have matched the URL to a route
        $matched_route = FALSE;
        // Loop through
        foreach ($this->routes as $route => $callback) {
            // Does the routing rule match the current URL?
            if (preg_match('#^' . $route . '$#', $this->url_clean, $matches)) {
                // A routing rule was matched
                $matched_route = TRUE;
                // Parameters to pass to the callback function
                $params = array(
                    "_URL" => $this->url_clean
                );
                // Get any named parameters from the route
                foreach ($matches as $key => $match) {
                    if (is_string($key)) {
                        $params[$key] = trim($match, '/');
                    }
                }
                ini_set('magic_quotes_runtime', 0);
                ini_get('magic_quotes_gpc') && !empty($_POST) && $_POST = stripslashes_deep($_POST); //$_POST处理
                $params['_POST'] = $_POST;
                ini_get('magic_quotes_gpc') && !empty($_GET) && $_GET = stripslashes_deep($_GET); //$_GET处理
                $params['_GET'] = $_GET;
                $params['_FILES'] = $_FILES;
                // Store the parameters and callback function to execute later
                self::$params = $params;

                //处理module
                strpos($callback[0],'/') === false && $callback[0]= 'default/'.$callback[0]; 
                list(self::$module,$controller)= explode('/',$callback[0]);
                if(self::$module !== 'default')
                    set_include_path(get_include_path() . PATH_SEPARATOR . APP_ROOT_PATH . "controllers" .DS.self::$module); 
    
                //add by chenjin 20140806 make the action be dynamic
                $k = trim($callback[1], '<>');
                if (strpos($callback[1], '<') === 0 && isset($params[$k])) {
                    $action = lcfirst(word_camelcase(str_replace('.', '_', $params[$k])));
                    $callback = (!method_exists(ucfirst($controller) . '_Controller', $action) ? $this->default_route : array(
                        $callback[0],
                        $action
                    ));
                }
                $this->callback = $callback;
                // Return the callback and params, useful for unit testing
                return array(
                    'callback' => $callback,
                    'params' => $params,
                    'route' => $route
                );
            }
        }
        // Was a match found or should we execute the default callback?
        if (!$matched_route && isset($this->default_route)) {
            $this->callback = $this->default_route;
            self::$params = array('_URL'=>$this->url_clean);
            $this->route = FALSE;            
        }
    }
    /**
     * Calls the appropriate callback function and passes the given parameters
     * given by Router::run()
     *
     * @since 2.0.1
     * @access public
     *
     * @return boolean False if the callback cannot be executed, true otherwise
     */
    public function dispatch() {
        if ($this->callback == NULL || !isset(self::$params)) {
            throw new Exception('No callback or parameters found, please run $router->run() before $router->dispatch()');
            return FALSE;
        }
        //hack by chenjin 20130317 check if the router is for the controller class
        $tmp = substr($this->callback[0],strlen(self::$module)+1);
        self::$controller = strtolower();
        self::$action = $this->callback[1];
        $real_controller_name = ucfirst(substr($this->callback[0],strlen(self::$module)+1))."_Controller";
        $function_return = call_user_func_array(array(
            new $real_controller_name,
            self::$action
        ) , array(
            self::$params
        ));
        if (isset($function_return)) { //如果有非对象的return，直接输出
            echo !is_scalar($function_return) ? json_encode($function_return, JSON_UNESCAPED_UNICODE) : $function_return;
        }
        return TRUE;
    }
    /**
     * Runs the router matching engine and then calls the dispatcher
     *
     * @uses Router::run()
     * @uses Router::dispatch()
     *
     * @since 2.0.1
     * @access public
     */
    public function execute() {
        $this->run();
        $this->dispatch();
    }
    /**
     * Adds a new URL routing rule to the routing table, after converting any of
     * our special tokens into proper regular expressions.
     *
     * @since 2.0.0 modify by chenjin 20130324
     * @access public
     *
     * @param array $routes The URL routing rules
     * @param mixed|array $callback The function or class + function to execute if this route is matched to the current URL
     *
     * @return boolean True if the route was added, false if it was not (If a conflict occured)
     */
    public function route($routes) {
        //$new_routes = array_map(array($this,'__route_tidy_preg'),$routes);
        $this->routes = $routes;
        //$this->routes = array_combine($new_routes,$callbacks);
        //$this->routes_original[$priority] = array_combine($new_routes,$routes);
        return $this;
    }
    /**
     * Retrieves the perl regular expression of $route
     *
     *
     * @add by chenjin 20130324
     * @access protected
     *
     * @param string $route The "dirty" $route
     *
     * @return string The tidy perl expression
     */
    function __route_tidy_preg($route) {
        // Make sure the route ends in a / since all of the URLs will
        //$route = rtrim( $route, '/' ) . '/';
        // Custom capture, format: <:var_name|regex>
        //strpos($route,':') !== false && $route = preg_replace( '/\<\:(.*?)\|(.*?)\>/', '(?P<\1>\2)', $route );
        // Alphanumeric capture (0-9A-Za-z-_), format: <:var_name>
        //strpos($route,'<:') !== false && $route = preg_replace( '/\<\:(.*?)\>/', '(?P<\1>[A-Za-z0-9\-\_]+)', $route );
        // Numeric capture (0-9), format: <#var_name>
        //strpos($route,'#') !== false && $route = preg_replace( '/\<\#(.*?)\>/', '(?P<\1>[0-9]+)', $route );
        // Wildcard capture (Anything INCLUDING directory separators), format: <*var_name>
        //$route = preg_replace( '/\<\*(.*?)\>/', '(?P<\1>.+)', $route );
        // Wildcard capture (Anything EXCLUDING directory separators), format: <!var_name>
        //strpos($route,'!') !== false && $route = preg_replace( '/\<\!(.*?)\>/', '(?P<\1>[^\/]+)', $route );
        // Add the regular expression syntax to make sure we do a full match or no match
        return '#^' . $route . '$#';
    }
    /**
     * Retrieves the part of the URL after the base (Calculated from the location
     * of the main application file, such as index.php), excluding the query
     * string. Adds a trailing slash.
     *
     * <code>
     * http://localhost/projects/test/users///view/1 would return the following,
     * assuming that /test/ was the base directory
     *
     * /users/view/1/
     * </code>
     *
     * @since 2.0.0
     * @access protected
     *
     * @param string $url The "dirty" url, not including the domain (path only)
     *
     * @return string The cleaned URL
     */
    protected function __get_clean_url($url) {
        // The request url might be /project/index.php, this will remove the /project part
        $url = (dirname($_SERVER['SCRIPT_NAME']) === '/' ? $url : str_replace(dirname($_SERVER['SCRIPT_NAME']) , '', $url));
        // Remove the query string if there is one
        $query_string = strpos($url, '?');
        if ($query_string !== FALSE) {
            $url = substr($url, 0, $query_string);
        }
        // If the URL looks like http://localhost/index.php/path/to/folder remove /index.php
        if (substr($url, 1, strlen(basename($_SERVER['SCRIPT_NAME']))) == basename($_SERVER['SCRIPT_NAME'])) {
            $url = substr($url, strlen(basename($_SERVER['SCRIPT_NAME'])) + 1);
        }
        // Make sure the URI ends in a /
        //$url = rtrim( $url, '/' ) . '/';
        // Replace multiple slashes in a url, such as /my//dir/url
        $url = preg_replace('/\/+/', '/', $url);
        return $url;
    }
}
