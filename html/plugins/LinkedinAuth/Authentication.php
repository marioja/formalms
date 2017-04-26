<?php
namespace Plugin\LinkedinAuth;
defined("IN_FORMA") or die('Direct access is forbidden.');

/* ======================================================================== \
|   FORMA - The E-Learning Suite                                            |
|                                                                           |
|   Copyright (c) 2013 (Forma)                                              |
|   http://www.formalms.org                                                 |
|   License  http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt           |
|                                                                           |
|   from docebo 4.0.5 CE 2008-2012 (c) docebo                               |
|   License http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt            |
\ ======================================================================== */



use OAuth\OAuth2\Service\Linkedin;
use OAuth\Common\Storage\Session;
use OAuth\Common\Consumer\Credentials;
use Get;
use Form;
use Lang;
use Docebo;

class Authentication extends \PluginAuthentication implements \PluginAuthenticationInterface {
    
    public static function getLoginGUI() {
        
        if(isset($_SESSION['social'])) {
            
            if($_SESSION['social']['plugin'] == Plugin::getName()) {
                
                return Get::img("social/linkedin-24.png") . " "
                        . Lang::t("_YOU_ARE_CONNECTING_SOCIAL_ACCOUNT", "social")
                        . " <b>" . $_SESSION['social']['data']['firstName'] . " " . $_SESSION['social']['data']['lastName'] . "</b>"
                        . Form::openForm("cancel_social", Get::rel_path("base"))
                          . Form::openButtonSpace()
                              . Form::getButton("cancel", "cancel_social", Lang::t("_CANCEL", "standard"))
                          . Form::closeButtonSpace()
                        . Form::closeForm();
            }
        } else {

            $linkedin_service = self::_getService();

            $url = $linkedin_service->getAuthorizationUri();

            return  "<a href='" . $url . "'>"
                      . Get::img("social/linkedin-24.png")
                  . "</a>";
        }
    }
    
    public static function getUserFromLogin() {
        
        $error  = Get::req("error", DOTY_STRING, false);
        $code   = Get::req("code", DOTY_STRING, false);
        
        if($error || !$code) return UNKNOWN_SOCIAL_ERROR;
        
        $linkedin_service = self::_getService();
        
        try {
            
            $linkedin_service->requestAccessToken($code);
            $user_info = json_decode($linkedin_service->request("/people/~?format=json"), true);
        } catch(Exception $e){
            
            return UNKNOWN_SOCIAL_ERROR;
        }
        
        if(empty($user_info['id'])) return EMPTY_SOCIALID;
        
        $user = \DoceboUser::createDoceboUserFromField("linkedin_id", $user_info['id'], "public_area");
        
        if(!$user) {
            
            $_SESSION['social'] = array(
                'plugin'    => Plugin::getName(),
                'data'      => $user_info
            );
            return USER_NOT_FOUND;
        }
        
        return $user;
    }
    
    public static function setSocial($id) {
        
        $query  = " UPDATE %adm_user"
                . " SET linkedin_id = '" . $id . "'"
                . " WHERE idst=" . Docebo::user()->getIdSt();
        
        sql_query($query);
    }
    
    private static function _getService() {
        
        // TODO: da qualche parte funzione httpsActive... ma serve davvero? O basta recuperare il protocollo utilizzato??
        $httpsActive = (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == "on")
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == "https") 
                || (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) == "on");
        
        $serviceFactory = new \OAuth\ServiceFactory();
        
        $storage = new Session(false);

        $credentials = new Credentials(
            Get::sett("linkedin.oauth_key"),
            Get::sett("linkedin.oauth_secret"),
            $httpsActive ? "https" : "http" . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?r=" . urlencode(_login_) . "&plugin=".Plugin::getName()
        );
        
        return $serviceFactory->createService("linkedin", $credentials, $storage, array("r_basicprofile"));
    }
}