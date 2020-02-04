<?php

namespace Wnbsmart\Larapatex;

class CpatexAPI
{
    protected $accessKey;         // API access key
    protected $secretKey;         // API secret
    protected $url;               // API base URL
    protected $recvWindow;        // API base URL
    protected $version = 'v2';    // API version
    protected $curl;              // curl handle

    /**
     * Constructor for CpatexAPI
     */
    function __construct()
    {
        $this->accessKey  = config('cpatex.auth.key');
        $this->secretKey  = config('cpatex.auth.secret');
        $this->url        = config('cpatex.urls.api');
        $this->wapi_url   = config('cpatex.urls.wapi');
        $this->recvWindow = config('cpatex.settings.timing');
        $this->curl       = curl_init();

        $curl_options     = [
            CURLOPT_SSL_VERIFYPEER => config('cpatex.settings.ssl'),
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_USERAGENT      => 'Cpatex PHP API Agent',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => 300
        ];

        curl_setopt_array($this->curl, $curl_options);

    }

    /**
     * Close CURL
     */
    function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * Key and Secret setter function. It's required for TRADE, USER_DATA, USER_STREAM, MARKET_DATA endpoints.
     * https://c-patex.com/documents/api_v2
     *
     * @param string $key    API Key
     * @param string $secret API Secret
     */
    function setAPI($key, $secret)
    {
        $this->accessKey = $key;
        $this->secretKey = $secret;
    }


    //------ PUBLIC API CALLS --------
    /*
    * getTicker
    * getCurrencies
    * getMarkets
    */

    /**
     * Get ticker
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTickers()
    {
        return $this->request($this->version.'/ticker/allPrices');
    }

    /**
     * Get ticker
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTicker($symbol)
    {
        $data = [
            'symbol' => $symbol
        ];
        return $this->request($this->version.'/ticker/allPrices', $data);
    }


    public function getCurrencies()
    {
        //Seems to be no such functionality
        return false;
    }

    /**
     * Current exchange trading rules and symbol information
     *
     * @return mixed
     * @throws \Exception
     */
    public function getMarkets()
    {
        return $this->request($this->version.'/markets');
        return $return['symbols'];
    }



    //------ PRIVATE API CALLS ----------
    /*
    * getBalances
    * getRecentTrades
    * getOpenOrders
    * getAllOrders
    * trade
    * marketSell
    * marketBuy
    * limitSell
    * limitBuy
    * depositAddress
    */

    /**
     * Get current account information
     *
     * @return mixed
     * @throws \Exception
     */
    public function getBalances() {

        $b = $this->privateRequest($this->version.'/account');
        return $b['balances'];

    }

    /**
     * Get trades for a specific account and symbol
     *
     * @param string $symbol Currency pair
     * @param int $limit     Limit of trades. Max. 500
     * @return mixed
     * @throws \Exception
     */
    public function getRecentTrades($symbol = 'BNBBTC', $limit = 500)
    {
        $data = [
            'symbol' => $symbol,
            'limit'  => $limit,
        ];

        $b = $this->privateRequest($this->version.'/myTrades', $data);
        return $b;

    }

    public function getOpenOrders()
    {


        $b = $this->privateRequest($this->version.'/openOrders');
        return $b;

    }

    public function getAllOrders($symbol)
    {

        $data = [
            'symbol' => $symbol
        ];
        $b = $this->privateRequest($this->version.'/allOrders', $data);
        return $b;

    }

    /**
     * Base trade function
     *
     * @param string $symbol   Asset pair to trade
     * @param string $quantity Amount of trade asset
     * @param string $side     BUY, SELL
     * @param string $type     MARKET, LIMIT, STOP_LOSS, STOP_LOSS_LIMIT, TAKE_PROFIT, TAKE_PROFIT_LIMIT, LIMIT_MAKER
     * @param bool $price      Limit price
     * @return mixed
     * @throws \Exception
     */
    public function trade($symbol, $quantity, $side, $type = 'MARKET', $price = false)
    {
        $data = [
            'symbol'   => $symbol,
            'side'     => $side,
            'type'     => $type,
            'quantity' => $quantity
        ];
        if($price !== false)
        {
            $data['price'] = $price;
        }

        $b = $this->privateRequest($this->version.'/order', $data, 'POST');

        return $b;
    }

    /**
     * Sell at market price
     *
     * @param string $symbol   Asset pair to trade
     * @param string $quantity Amount of trade asset
     * @return mixed
     * @throws \Exception
     */
    public function marketSell($symbol, $quantity)
    {
        return $this->trade($symbol, $quantity, 'SELL', 'MARKET');
    }

    /**
     * Buy at market price
     *
     * @param string $symbol   Asset pair to trade
     * @param string $quantity Amount of trade asset
     * @return mixed
     * @throws \Exception
     */
    public function marketBuy($symbol, $quantity)
    {
        return $this->trade($symbol, $quantity, 'BUY', 'MARKET');
    }

    /**
     * Sell limit
     *
     * @param string $symbol   Asset pair to trade
     * @param string $quantity Amount of trade asset
     * @param float $price     Limit price to sell
     * @return mixed
     * @throws \Exception
     */
    public function limitSell($symbol, $quantity, $price)
    {
        return $this->trade($symbol, $quantity, 'SELL', 'LIMIT', $price);
    }

    /**
     * Buy limit
     *
     * @param string $symbol   Asset pair to trade
     * @param string $quantity Amount of trade asset
     * @param float $price     Limit price to buy
     * @return mixed
     * @throws \Exception
     */
    public function limitBuy($symbol, $quantity, $price)
    {
        return $this->trade($symbol, $quantity, 'BUY', 'LIMIT', $price);
    }



    /**
     * Deposit Address
     * @param string $symbol   Asset symbol
     * @return mixed
     **/
    public function depositAddress($symbol) {

        return $this->wapiRequest($this->version."/depositAddress.html", ['asset' => $symbol]);

    }

    //------ REQUESTS FUNCTIONS ------

    /**
     * Make public requests (Security Type: NONE)
     *
     * @param string $url    URL Endpoint
     * @param array $params  Required and optional parameters
     * @param string $method GET, POST, PUT, DELETE
     * @return mixed
     * @throws \Exception
     */
    private function request($url, $params = [], $method = 'GET')
    {
        // Set URL & Header
        curl_setopt($this->curl, CURLOPT_URL, $this->url . $url);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array());

        //Add post vars
        if($method == 'POST')
        {
            curl_setopt($this->curl, CURLOPT_POST, count($params));
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $params);
        }

        //Get result
        $result = curl_exec($this->curl);
        if($result === false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

        // decode results
        $result = json_decode($result, true);

        if(!is_array($result) || json_last_error())
            throw new \Exception('JSON decode error');

        return $result;

    }

    /**
     * Make private requests (Security Type: TRADE, USER_DATA, USER_STREAM, MARKET_DATA)
     *
     * @param string $url    URL Endpoint
     * @param array $params  Required and optional parameters
     * @param string $method GET, POST, PUT, DELETE
     * @return mixed
     * @throws \Exception
     */
    private function privateRequest($url, $params = [], $method = 'GET')
    {
        // build the POST data string
        $params['timestamp']  = number_format((microtime(true) * 1000), 0, '.', '');
        $params['recvWindow'] = $this->recvWindow;

        $query   = http_build_query($params, '', '&');

        // set API key and sign the message
        $sign    = hash_hmac('sha256', $query, $this->secretKey);

        $headers = array(
            'X-MBX-APIKEY: ' . $this->accessKey
        );

        // make request
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        // build the POST data string
        $postdata = $params;

        // Set URL & Header
        curl_setopt($this->curl, CURLOPT_URL, $this->url . $url."?{$query}&signature={$sign}");

        //Add post vars
        if($method == "POST") {
            curl_setopt($this->curl,CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, array());
        }

        //Get result
        $result = curl_exec($this->curl);
        if($result === false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

        // decode results
        $result = json_decode($result, true);
        if(!is_array($result) || json_last_error())
            throw new \Exception('JSON decode error');

        return $result;

    }

    /**
     * Make wapi requests
     *
     * @param string $url    URL Endpoint
     * @param array $params  Required and optional parameters
     * @param string $method GET, POST, PUT, DELETE
     * @return mixed
     * @throws \Exception
     */
    private function wapiRequest($url, $params = [], $method = 'GET')
    {
        // build the POST data string
        $params['timestamp']  = number_format((microtime(true) * 1000), 0, '.', '');
        $params['recvWindow'] = $this->recvWindow;

        $query   = http_build_query($params, '', '&');

        // set API key and sign the message
        $sign    = hash_hmac('sha256', $query, $this->secretKey);

        $headers = array(
            'X-MBX-APIKEY: ' . $this->accessKey
        );

        // make request
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, $headers);

        // build the POST data string
        $postdata = $params;

        // Set URL & Header
        curl_setopt($this->curl, CURLOPT_URL, $this->wapi_url . $url."?{$query}&signature={$sign}");

        //Add post vars
        if($method == "POST") {
            curl_setopt($this->curl,CURLOPT_POST, 1);
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, array());
        }

        //Get result
        $result = curl_exec($this->curl);
        if($result === false)
            throw new \Exception('CURL error: ' . curl_error($this->curl));

        // decode results
        $result = json_decode($result, true);
        if(!is_array($result) || json_last_error())
            throw new \Exception('JSON decode error');

        return $result;

    }

}