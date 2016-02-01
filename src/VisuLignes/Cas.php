<?php 


namespace VisuLignes;

include('src/VisuLignes/httpful.phar');

class Cas{
    protected $url;
    protected $timeout;
    
    public function __construct($url, $timeout=10){
        $this->url = $url;
        $this->timeout = $timeout;
    }
    
    public function authenticate($ticket, $service){
        $r = \Httpful\Request::get($this->getValidateUrl($ticket, $service))
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
        $r->body = str_replace("\n", "", $r->body);
        var_dump($r->body);
        try {
            $xml = new \SimpleXMLElement($r->body);
        }catch (\Exception $e) {
            throw new \Exception("Return cannot be parsed :\n {$r->body}", 1);
        }
        
        $namespaces = $xml->getNamespaces();
        
        $serviceResponse = $xml->children($namespaces['cas']);
        $user = $serviceResponse->authenticationSuccess->user;
        
        if ($user) {
            return (string)$user; // cast simplexmlelement to string
        }
        else {
            $authFailed = $serviceResponse->authenticationFailure;
            if ($authFailed) {
                $attributes = $authFailed->attributes();
                throw new \Exception("AuthenticationFailure : ".$attributes['code']." ($ticket, $service)", 1);
            }
            else {
                throw new \Exception("Cas return is weird : '{$r->body}'", 1);
            }
        }
        // never reach there
    }

    public function logout(){
        $r = \Httpful\Request::get($this->url."logout")
          ->sendsXml()
          ->timeoutIn($this->timeout)
          ->send();
        $r->body = str_replace("\n", "", $r->body);
        try {
            $xml = new SimpleXMLElement($r->body);
            return true;
        }catch (\Exception $e) {
            return false;
        }
    }
    
    public function getValidateUrl($ticket, $service){
        return $this->url."serviceValidate?ticket=".urlencode($ticket)."&service=".urlencode($service);
    }
}