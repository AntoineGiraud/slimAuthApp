<?php

namespace CoreHelpers;

/**
* A Helper class for links in view pages
*/
class RouteHelper {

    public $app;
    public $request;
    public $response;
    public $Auth;
    public $flash;

    public $publicPath;
    public $curPageUri;
    public $curPage;
    public $curPageBasePath;
    public $curPageBaseUrl;
    public $curPageUrl;

    public $pageName;
    public $webSiteTitle;

    function __construct($app, $request, $response, $pageName){
        $this->app = $app;
        $this->Auth = $app->Auth;
        $this->flash = $app->flash;
        $this->request = $request;
        $this->response = $response;
        $this->conf = $app->get('settings');

        $this->publicPath = $app->get('settings')['public_path'];
        $this->publicUrl = $app->get('settings')['public_url'];
        $this->curPageUri = $request->getUri();
        $this->curPage = $request->getUri()->getPath();
        $this->curPageBasePath = $request->getUri()->getbasePath();
        $this->curPageBaseUrl = $request->getUri()->getBaseUrl();
        $this->curPageUrl = $request->getUri()->getBaseUrl() . '/' . $this->curPage;

        $this->pageName = $pageName;
        $this->webSiteTitle = $app->get('settings')['webSiteTitle'];
    }

    function returnWithFlash($redirect, $msg, $flashType='danger', $withStatus=null) {
        if (!empty($msg))
            $this->flash->addMessage($flashType, $msg);
        if (!empty($withStatus) && (int)$withStatus)
            return $this->response->withHeader('Location', $this->getPathFor($redirect))->withStatus((int)$withStatus);
        else return $this->response->withHeader('Location', $this->getPathFor($redirect));
    }

    public function getPathFor($page=''){
        return $this->curPageBasePath . '/' . $page;
    }

    public function getPageTitle(){
        return $this->pageName . ' - ' . $this->webSiteTitle;
    }

    public function showLinkLi($page, $label, $args="", $curPage=""){
        if ($curPage != '/' && substr($curPage, 0, 1) == "/")
            $curPage = substr($curPage, 1);
        if ($this->Auth->memberCanAccessPages($page)) {
            return '<li'.($curPage == $page ?' class="active"':'').'><a href="'.$this->getPathFor($page) . $args.'">'.$label.'</a></li>';
        }
        return '';
    }

    public static function cleanAccentuations($value) {
        $return = $value;
        $return = strtolower(trim($return));
        $return = str_replace(array('(',')','{','}'), '', $return);
        $return = str_replace(array(' - '), '-', $return);
        $return = str_replace(array(' ','\''), '-', $return);
        $return = self::cleanAccentuationsOnly($return);
        return $return;
    }
    public static function cleanAccentuationsOnly($value) {
        $value = str_ireplace(['é','è','ê','ë'], 'e', $value);
        $value = str_ireplace(['à','â','ä'],     'a', $value);
        $value = str_ireplace(['ï','î'],         'i', $value);
        $value = str_ireplace(['ö','ô'],         'o', $value);
        $value = str_ireplace(['û','ü'],         'u', $value);
        return $value;
    }
}