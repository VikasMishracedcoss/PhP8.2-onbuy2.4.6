<?php


namespace Ced\Onbuy\Helper;

use Ced\Onbuy\Helper\Logger;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Ced\Onbuy\Model\AccountsFactory; 


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const API_URL_SANDBOX = "https://api.onbuy.com/v2/";
    const API_URL = "https://api.onbuy.com/v2/";



    protected $scopeConfig;
    public $apiMode;
    public $apiUrl;
    public $registry;
    public $accounts;


    public $fileIo;
    public $_allowedFeedType = array();

    /**
     * @var mixed
     */
    public $permissions;

    /**
     * @var mixed
     */
    public $oauthCallback;

    /**
     * @var mixed
     */
    public $oauthConsumerKey;

    /**
     * @var mixed
     */
    public $oauthConsumerSecret;

    /**
     * @var mixed
     */
    public $oauthToken;

    /**
     * @var mixed
     */
    public $oauthTokenSecret;

    /**
     * @var string
     */
    public $requestTokenUrl;

    /**
     * @var string
     */
    public $authoriseTokenUrl;

    /**
     * @var string
     */
    public $accessTokenUrl;

    /**
     * @var array
     */
    public $authParams;
    public $directoryList;
    public $json;
    public $adminSession;
    public $multiAccountHelper;
    public $logger;
    public $token;
    public $tokenExpiration;
    public $secret;
    public $key;
    public $_coreRegistry;


    public function __construct(
        Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Json\Helper\Data $json,
        \Magento\Backend\Model\Session $session,
        \Ced\Onbuy\Helper\MultiAccount $multiAccountHelper,
        AccountsFactory $accounts,
        \Magento\Framework\Registry $registry,
        Logger $logger,
        DirectoryList $directoryList
    )
    {
        parent::__construct($context);
        $this->accounts = $accounts;
        $this->scopeConfig = $scopeConfig;
        $this->directoryList = $directoryList;
        $this->json = $json;
        $this->adminSession = $session;
        $this->multiAccountHelper = $multiAccountHelper;
        $this->_coreRegistry = $registry;
        $this->logger = $logger;


        $this->apiUrl = $this->apiMode == 'sandbox' ? self::API_URL_SANDBOX :self::API_URL;

    }
   
    public function getRequest($url, $authentication = false)
    {

       
        $token = $this->generateAccessToken();
        $url = $this->apiUrl.$url;
    
        $request = null;
        $header = [];
        $response = null;
        try {
            $request = curl_init();
            if ($authentication){

                $randomString = rand(10,100);
                $header = array(
                    "Authorization: $token",
                    "cache-control: no-cache",
                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                    "Provider-Token: 006B6CEAAC244F1F966573B3218B3F2A"
                );
            }
///////////////////////////////////////////////


 $curl = curl_init();

// curl_setopt_array($curl, array(
//   CURLOPT_URL => 'https://api.onbuy.com/v2/listings/check-winning?site_id=2000&skus[0]=21721',
//   CURLOPT_RETURNTRANSFER => true,
//   CURLOPT_ENCODING => '',
//   CURLOPT_MAXREDIRS => 10,
//   CURLOPT_TIMEOUT => 0,
//   CURLOPT_FOLLOWLOCATION => true,
//   CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//   CURLOPT_CUSTOMREQUEST => 'GET',
//   CURLOPT_HTTPHEADER => $header,
// ));

// $response = curl_exec($curl);

///////////////////////////////////////

            curl_setopt($request, CURLOPT_URL, $url);
            curl_setopt($request, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($request, CURLOPT_HTTPHEADER , $header);
            curl_setopt($request, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($request, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($request);
            
            $errors = curl_error($request);
            if (!empty($errors)) {
                curl_close($request);
                throw new \Exception($errors);
            }
            curl_close($request);
       //print_r($response); die(__DIR__);
            return $response;
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);

            return false;
        }
    }

    
   public function Deliver_template()
   {   
       $token = $this->generateAccessToken();
       $url= 'sellers/deliveries?site_id=2000&limit=100&offset=0';
       $jsonResponse = $this->getRequest($url,$token);
       $response = json_decode($jsonResponse, true);
       //print_r($response['results']); die;
        
        foreach($response['results'] as $key=>$responsedata){
            //print_r($responsedata);
           // $data[] =$responsedata['template_name']; 
            $data[] = $responsedata['seller_delivery_template_id'];//.':'.$responsedata['template_name']; 
           }
        //die;
       $List = array_unique($data);
       //print_r($List);
       $List1 = implode(', ',$List);
       //print_r($List1); die();
       return $List1;
   }




    public function postRequest($url, $body)
    {


        $request = null;
        $response = null;
        try {
            $token = $this->generateAccessToken();
            $randomString = rand(10,100);
            $header = array(
                "authorization: $token",
                "cache-control: no-cache",
                "content-type: application/json",
                "Provider-Token: 006B6CEAAC244F1F966573B3218B3F2A");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"/*"PUT"*/);
            curl_setopt($ch, CURLOPT_POSTFIELDS, /*json_encode*/($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }
            curl_close($ch);
           
            return $body;
        } catch(\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }

    public function putRequest($url, $body)
    {
        $request = null;
        $response = null;
        try {
            $token = $this->generateAccessToken();
            $randomString = rand(10,100);
            $header = array(
                "authorization: $token",
                "cache-control: no-cache",
                "content-type: application/json",
                "Provider-Token: 006B6CEAAC244F1F966573B3218B3F2A");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
            curl_setopt($ch, CURLOPT_POSTFIELDS, /*json_encode*/($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }
    public function delRequest($url, $body)
    {

        $request = null;
        $response = null;
        try {
            $token = $this->generateAccessToken();
            $randomString = rand(10,100);
            $header = array(
                "authorization: $token",
                "cache-control: no-cache",
                "content-type: application/json",
                "Provider-Token: 006B6CEAAC244F1F966573B3218B3F2A");
            /*echo "<pre>";print_r($url);
        print_r($header);
        print_r(json_encode($body));die;*/
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_POSTFIELDS, /*json_encode*/($body));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            if (!empty($servererror)) {
                $request = curl_getinfo($ch);
                curl_close($ch);
                throw new \Exception($servererror);
            }
            curl_close($ch);
            return $body;
        } catch(\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            return false;
        }
    }

    public function fetchToken($params)
    {
        $response = [];
        $this->requestTokenUrl = $params['account_env'] == 'sandbox' ? self::OAUTH_REQUEST_TOKEN_URL_SANDBOX :
            self::OAUTH_REQUEST_TOKEN_URL;
        $this->authoriseTokenUrl = $params['account_env'] == 'sandbox' ? self::OAUTH_AUTHORISE_TOKEN_URL_SANDBOX
            :self::OAUTH_AUTHORISE_TOKEN_URL;
        try {
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth 
                oauth_consumer_key=".$params['outh_consumer_key'].",
                oauth_version=1.0,
                oauth_timestamp=".time().",
                oauth_nonce=ced.".$randomString.",
                oauth_signature_method=PLAINTEXT,
                oauth_signature=".$params['outh_consumer_secret']."&";
            $ch = curl_init();
            $url = $this->requestTokenUrl."?scope=MyOnbuyRead,MyOnbuyWrite,BiddingAndBuying";

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt ($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($server_output, $header_size);
            $this->adminSession->setSessId($params['id']);
            curl_close($ch);
            if (isset($body)) {
                $match=strpos($body,'oauth_token_secret=');
                if(isset($match) && $match!=0){
                    $authResponse = explode('&', $body);
                    if (isset($authResponse[0])) {
                        $response['status'] = 'success';
                        $response['message'] = $authResponse;
                        $response['url'] = $this->authoriseTokenUrl . "?".$authResponse[0];
                    } else {
                        $response['status'] = 'error';
                        $response['message'] = "please check the credentials";
                    }
                }else{
                    $response['status'] = 'error';
                    $response['message'] = "please check the credentials";
                }
            }

        }catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
      //print_r($response); die(__DIR__);
        return $response;
    }

    public function validateToken($params)
    {
        try {
            $randomString = rand(10,100);
            $header[] = "Content-Type: application/json";
            $header[] = "Authorization: OAuth oauth_verifier=".$params['outh_verifier'].",
                oauth_consumer_key=".$this->oauthConsumerKey.",
                oauth_token=".$this->oauthToken.",
                oauth_version=1.0,
                oauth_timestamp=".time().",
                oauth_nonce=ced.".$randomString.",
                oauth_signature_method=PLAINTEXT,
                oauth_signature=".$this->oauthConsumerSecret."&".$this->oauthTokenSecret;

            $ch = curl_init();
            $url = $this->accessTokenUrl;

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt ($ch, CURLOPT_POST, 1 );
            curl_setopt($ch, CURLOPT_POSTFIELDS, array());
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $server_output = curl_exec($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($server_output, $header_size);
            curl_close($ch);

            $authResponse = explode('&', $body);
            if (isset($authResponse[0]) && isset($authResponse[1])) {
                $oauth_token = str_replace("oauth_token=", "", $authResponse[0]);
                $tokenSecretWithMsg=  str_replace("oauth_token_secret=", "", $authResponse[1]);
                $oauth_token_secret = explode('{', $tokenSecretWithMsg);
                $finalResponse = array('oauth_token' => $oauth_token, 'oauth_token_secret' => $oauth_token_secret[0]);
                $response['status'] = 'success';
                $response['message'] = $finalResponse;
            } else {
                $response['status'] = 'error';
                $response['message'] = $authResponse[0];
            }
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
        }
        //print_r($response); die(__DIR__);
        return $response;
    }

    public function getOrders()
    {
        $response = array();
        try {
            //todo provide filter settings
            $orderFilter ='open'/*$this->scopeConfig->getValue('trademe_config/order/order_filter')*/;
            $startDate = strtotime(date('Y-m-d').' -30 days');
            $startDate = date('Y-m-d', $startDate);
            $startDate  = $startDate.' '.date("h:i:s");
            $url = $this->apiUrl."orders?site_id=2000&filter=[status]=open&limit=100&offset=0&filter[order_ids]&filter[modified_since]=$startDate&sort[created]=desc";
            $randomString = rand(10,100);
            $token = $this->generateAccessToken();
            $header = array(
                "authorization: $token",
                "cache-control: no-cache",
                "content-type: application/json",
                "Provider-Token: 006B6CEAAC244F1F966573B3218B3F2A");

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $servererror = curl_error($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $body = substr($response, $header_size);
            curl_close($ch);


            $response = json_decode($body, true);
        } catch (\Exception $e) {
            $this->logger->addError($e->getMessage(), ['path' => __METHOD__]);
            $response['errors'] = true;
            $response['data'] = $e->getMessage();
        }
        return $response;
    }


    public function fetchAllCategories()
    {
        $url = $this->apiUrl."Categories.json";
 
        $jsonResponse = $this->getRequest($url);
        $response = json_decode($jsonResponse, true);
        
        return $response;
    }

    public function loadFile($path, $code = '', $type = '')
    {
        if (!empty($code)) {
            $path = $this->directoryList->getPath($code) . "/" . $path;
        }
        if (file_exists($path)) {
            $pathInfo = pathinfo($path);
            if ($pathInfo['extension'] == 'json') {
                $myfile = fopen($path, "r");
                $data = fread($myfile, filesize($path));
                fclose($myfile);
                if (!empty($data)) {
                    $data = empty($type) ? $this->json->jsonDecode($data) : $data;
                    return $data;
                }
            }
        }
        return false;
    }
    public function fetchCatAttr($catId)
    {
        $url = $this->apiUrl."Categories/".$catId."/Details.json";
        $jsonResponse = $this->getRequest($url);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function setAccountSession() {
        $accountId = '';
        $this->adminSession->unsAccountId();
        $params = $this->_getRequest()->getParams();
        if(isset($params['account_id']) && $params['account_id'] > 0) {
            $accountId = $params['account_id'];
            // print_r($accountId); die('1');
        } else {
            $accountId = $this->scopeConfig->getValue('onbuy_config/product_upload/primary_account');
            if(!$accountId) {
                $accounts = $this->multiAccountHelper->getAllAccounts(true);
                if($accounts) {
                    $accountId = $accounts->getFirstItem()->getId();
                }
            }

        }
        $this->adminSession->setAccountId($accountId);
        return $accountId;
    }

    public function getAccountSession() {
        $accountId = '';
        $accountId = $this->adminSession->getAccountId();

        if(!$accountId) {
            $accountId = $this->setAccountSession();
        }
       
        return $accountId;
    }

    public function updateAccountVariable($accountId = null)
    {
        $account = false;
        if ($this->_coreRegistry->registry('onbuy_account')) {
            $account = $this->_coreRegistry->registry('onbuy_account');
        }
        if ($accountId)
            $account = $this->accounts->create()->load($accountId);

        $this->adminSession->setAccountId($account->getId());
        $this->apiMode = ($account) ? htmlspecialchars($account->getAccountEnv() ?? '') : '';
        $this->oauthToken = ($account) ? htmlspecialchars($account->getOuthAccessToken() ?? '') : '';
        $this->oauthTokenSecret = ($account) ? htmlspecialchars($account->getOuthTokenSecret() ?? '') : '';
        $this->oauthConsumerKey = ($account) ? htmlspecialchars($account->getOuthConsumerKey() ?? '') : '';
        $this->oauthConsumerSecret = ($account) ? htmlspecialchars($account->getOuthConsumerSecret() ?? '') : '';
        $this->accessTokenUrl = ($account) ? htmlspecialchars($account->getAccountEnv()?? '') : '';

        $this->apiUrl = (($account) ? htmlspecialchars($account->getAccountEnv()?? '') : '') == 'sandbox' ? self::API_URL_SANDBOX
            :self::API_URL;
        $this->token = ($account) ? htmlspecialchars($account->getAccessToken()?? '') : '';
        $this->tokenExpiration = ($account) ? htmlspecialchars($account->getTokenExpiration()?? '') : '';
        $this->secret = ($account) ? htmlspecialchars($account->getConsumerSecret()?? '') : '';
        $this->key = ($account) ? htmlspecialchars($account->getConsumerKey()?? '') : '';

    }

    public function productUpload($value, $type = 'upload')
    {
        $url = $this->apiUrl.'products';
        if ($type == 'update'){
              $jsonResponse = $this->putRequest($url, $value);
          }else{
            $jsonResponse = $this->postRequest($url, $value);
          }
           
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function createListing($value)
    {
       
        $url = $this->apiUrl.'listings';
 
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function processQueue($value)
    {

        $url = 'queues/'. $value .'?site_id='. 2000;
        $jsonResponse = $this->getRequest($url, true);
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function productSync($value)
    {
        $url = $this->apiUrl.'listings/by-sku';

        $jsonResponse = $this->putRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function getProductData($value)
    {
        
        //todo remove fake response
        $url ='listings?site_id=2000&filter[sku]='.$value;
        $url ='products?site_id=2000&filter[query]=648951034073&filter[field]=product_code'/*$value*/;
      
        $jsonResponse = $this->getRequest($url, true);
        $response = json_decode($jsonResponse, true);
        return $response;
    }


    public function getCurrentProducts()
    {
        $url = $this->apiUrl.'MyOnbuy/SellingItems/ALL.json';
        $jsonResponse = $this->getRequest($url, true);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function imageUpload($value)
    {
        $url =$this->apiUrl;
        $url = $url.'Photos/Add.json';
        $jsonResponse = $this->postRequest($url, $value);

        $response = json_decode($jsonResponse, true);
        return $response;
    }
    public function updatePhoto($ids,$listingId){
        $url = $this->apiUrl;
        $url = $url.'Photos/'.$ids.'/Add/'.$listingId.'.json';
        $jsonResponse = $this->postRequest($url);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function withdrawListing($value)
    {
        $url = $this->apiUrl.'listings/by-sku';
        $jsonResponse = $this->delRequest($url, $value);

        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function productRelist($value)
    {
        $url = $this->apiUrl.'Selling/Relist.json';
        $jsonResponse = $this->postRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function createShipmentOrderBody($value)
    {
        $url = $this->apiUrl.'orders/dispatch';
        $jsonResponse = $this->putRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        // print_r($response);
        // die(__FILE__);
        return $response;
    }
    public function orderRefund($value)
    {
        $url = $this->apiUrl.'orders/refund';
        $jsonResponse = $this->putRequest($url, $value);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function generateAccessToken()
    {
        $result = true;
        $result = $this->calculateTokenExpiration($this->tokenExpiration);
        if (true)
        {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://api.onbuy.com/v2/auth/request-token",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"secret_key\"\r\n\r\n$this->secret\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"consumer_key\"\r\n\r\n$this->key\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
                CURLOPT_HTTPHEADER => array(
                    "cache-control: no-cache",
                    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW",
                    "postman-token: 2f5dcaf2-5933-7103-55b1-ceca38da2f6d"
                ),
            ));
            $response = curl_exec($curl);
            $token = json_decode($response,1);
            if ($this->_coreRegistry->registry('onbuy_account'))
            {
                $account = $this->_coreRegistry->registry('onbuy_account');
                $account->setAccessToken(isset($token['access_token']) ? $token['access_token'] : '');
                $account->setTokenExpiration(isset($token['expires_at']) ? $token['expires_at'] : '');
                $account->save();
                $this->token = isset($token['access_token']) ? $token['access_token'] : '';
            }
            return isset($token['access_token']) ? $token['access_token'] : null;
        } else{
            return $this->token;
        }


    }

    public function calculateTokenExpiration($expiration)
    {
        try {
            if($expiration)
            {
                $start = gmdate("Y-m-d H:i:s", time());
                $end = gmdate("Y-m-d H:i:s", $expiration);
                $start_date = new \DateTime($start);
                $since_start = $start_date->diff(new \DateTime($end));
                if($since_start->i > 14)
                {
                    return true;
                }else{
                    return false;
                }
            }
        } catch (\Exception $e){
            $this->logger->addError("In Token Expiration Calculation : ". $e->getMessage(), ['path' => __METHOD__]);
            return true;
        }
    } 


}

