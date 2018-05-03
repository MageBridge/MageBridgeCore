<?php
/**
 *
 */
namespace Yireo\Common\Router;

/**
 * Class Base
 *
 * @package Yireo\Router
 */
class Base
{
    /**
     * @var string
     */
    protected $request;

    /**
     * @var
     */
    protected $routes;

    protected $rootFolder;

    /**
     * Base constructor.
     *
     * @param $request string
     */
    public function __construct($request = null, $routes = [], $rootFolder = null)
    {
        $this->setRequest($request);
        $this->setRoutes($routes);
        $this->setRootFolder($rootFolder);
    }

    /**
     * @return mixed
     */
    public function getRootFolder()
    {
        return $this->rootFolder;
    }

    /**
     * @param mixed $rootFolder
     */
    public function setRootFolder($rootFolder = null)
    {
        if (empty($rootFolder)) {
            $rootFolder = ROOT_FOLDER;
        }

        $this->rootFolder = $rootFolder;
    }

    /**
     * @param $domains
     *
     * @return $this
     */
    public function setForceSsl($domains)
    {
        foreach ($domains as $domain) {
            if ($_SERVER['HTTP_HOST'] == $domain && !isset($_SERVER['HTTPS'])) {
                header('Location: https://' . $domain . '/');
                exit;
            }
        }

        return $this;
    }

    /**
     * @param $request
     *
     * @return $this
     */
    public function setRequest($request)
    {
        $request = preg_replace('/^\//', '', $request);
        $this->request = $request;

        return $this;
    }

    /**
     * @param $routes array
     *
     * @return $this
     */
    public function setRoutes($routes)
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * @return $this
     *
     * @throws \Yireo\Common\Exception\PageNotFound
     */
    public function route($defaultPage = null)
    {
        $page = 'main.php';

        if (!empty($defaultPage)) {
            $page = $defaultPage;
        }

        foreach ($this->routes as $route) {
            $routePage = $route['page'];
            $possibleRoutes = $route['routes'];

            if ($this->request == $routePage) {
                header('Location: //' . $_SERVER['HTTP_HOST'] . '/' . $possibleRoutes[0]);
                exit;
            }

            if (in_array($this->request, $possibleRoutes)) {
                $page = $routePage;
                break;
            }
        }

        if (empty($route)) {
            throw new \Yireo\Common\Exception\PageNotFound('Page not found for '.$this->request);
        }

        $dataFile = $this->rootFolder . '/data/' . $route['data_file'];
        $this->displayPage($page, $dataFile);

        return $this;
    }

    /**
     * @param string $page
     *
     * @throws \Yireo\Common\Exception\PageNotFound
     */
    public function displayPage($page = 'main.php', $dataFile)
    {
        $pagePath = $this->rootFolder . '/content/pages/' . $page;

        if (!file_exists($pagePath)) {
            throw new \Yireo\Common\Exception\PageNotFound('Page not found for '.$page);
        }

        $this->sendHeaders();
        include $this->rootFolder . '/data/index.php';

        $content = new \Yireo\Content\Helper($dataFile);

        include_once $pagePath;
    }

    /**
     *
     */
    public function sendHeaders()
    {
        header('Expires: ' . date('r'));
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');
    }
}
