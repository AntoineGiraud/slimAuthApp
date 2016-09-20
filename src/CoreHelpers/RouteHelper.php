<?php

namespace CoreHelpers;

/**
* A Helper class for links in view pages
*/
class RouteHelper {

    public $app;

    public $publicPath;
    public $curPageUri;
    public $curPage;
    public $curPageBasePath;
    public $curPageBaseUrl;
    public $curPageUrl;

    public $pageName;
    public $webSiteTitle;

    function __construct($app, $request, $pageName){
        $this->app = $app;

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

    public function getPathFor($page=''){
        return $this->curPageBasePath . '/' . $page;
    }

    public function getPageTitle(){
        return $this->pageName . ' - ' . $this->webSiteTitle;
    }

    public function showLinkLi($page, $label, $args=""){
        global $Auth;
        if ($Auth->memberCanAccessPages($page)) {
            return '<li><a href="'.$this->getPathFor($page) . $args.'">'.$label.'</a></li>';
        }
        return '';
    }
}