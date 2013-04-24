<?php

class Kwgl_Curl{

    private $oRequest;
    public  $sResponse;
    public  $sHeader;

    // supported options
    private $_supportedOptions = array("optReturnResponse","optReturnHeader","optWriteFunction","optVebose","optFollowLocation","optCookieFile","optCookieJar");

    public  $bOptReturnResponse = false;		// default the response is outputted
    public  $bOptReturnHeader = false;			// default the header is not written
    public  $bOptVebose = false;
    public  $bOptFollowLocation = false;		// default location redirects are not followed
    public  $sOptStandardError = "/Users/macbookpro/Sites/shares/var/logs/curl.txt";
	public  $bOptCookieJar = false;
	public  $bOptCookieFile = false;
	public  $sCookieJar = "";

    /*
     * @description initialized the request object
     */
    public function __construct($sUrl,$sCookieJar = null){
		$this->oRequest = curl_init($sUrl);
		if($sCookieJar != null){
			$this->sCookieJar = $sCookieJar;
		}
    }

    /*
     * @description sets the value of a supported option
     */
    public function __set($sVariable,$sValue){

		if(in_array($sVariable,$this->_supportedOptions)){
			$this->$sVariable = $sValue;
		} else {
			return false;
		}

    }

    /*
     * @description prepares the request for a http post
     */
    public function post($aFields){

		if(is_array($aFields)){

			$i = 0;
			foreach($aFields AS $iKey => $sValue){

				$sSeperator = ($i != 0)? "&" : "";
				$sBody .= $sSeperator.$iKey."=".$sValue;
				$i++;
			}

		} else {
			$sBody = $aFields;
		}

		// opt-in the needed data for sending a http post request
		curl_setopt($this->oRequest,CURLOPT_POST,true);
		curl_setopt($this->oRequest,CURLOPT_POSTFIELDS,$sBody);

		$this->execute();

    }

    public function put($sFile){

	curl_setopt($this->oRequest,CURLOPT_PUT,true);

	$oFh  = fopen($sFile, 'r');
	curl_setopt($this->oRequest, CURLOPT_INFILE, $oFh);
	fclose($oFh);

	$this->execute();

    }

    /*
     * @description prepares the request for a http get
     */
    public function get(){
		$this->execute();
    }

    /*
     * @description does execute the prepared request and stores the response for later use
     */
    private function execute(){

		if($this->bOptCookieFile){
			curl_setopt($this->oRequest,CURLOPT_COOKIEFILE,$this->sCookieJar);
		}

		if($this->bOptCookieJar){
			curl_setopt($this->oRequest,CURLOPT_COOKIEJAR,$this->sCookieJar);
		}

		if($this->bOptFollowLocation){
			curl_setopt($this->oRequest,CURLOPT_FOLLOWLOCATION,true);
		}

		// when the header needs to be received
		if($this->bOptReturnHeader){
			$this->sHeader = curl_getinfo($this->oRequest);
		}

		// when the vebose should be received
		if($this->bOptVebose){
			curl_setopt($this->oRequest,CURLOPT_VERBOSE,true);
			$oFh  = fopen($this->optStandardError, 'w+');
			curl_setopt($this->oRequest,CURLOPT_STDERR,$oFh);
			fclose($oFh);
		}

		curl_setopt($this->oRequest,CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.2) Gecko/20090729 Firefox/3.5.2 GTB5');

		// when the response needs to be received
		if($this->bOptReturnResponse){
			curl_setopt($this->oRequest,CURLOPT_RETURNTRANSFER,true);
			$this->sResponse = curl_exec($this->oRequest);
		} else {
			curl_exec($this->oRequest);
		}

		//echo curl_getinfo($this->request, CURLINFO_HTTP_CODE);

		// close the connection
		curl_close($this->oRequest);

    }

}