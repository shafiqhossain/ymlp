<?php

namespace Drupal\ymlp;

/**
 * Ymlp library.
 *
 * @package Ymlp
 */
class YmlpApi {

  /**
   * Error Message
   *
   * @var String $errorMessage
   */
  protected $errorMessage;

  /**
   * Api Url
   *
   * @var String $apiUrl
   */
  protected $apiUrl = "www.ymlp.com/api/";

  /**
   * Api Username
   *
   * @var String $apiUsername
   */
  protected $apiUsername;

  /**
   * Api Key
   *
   * @var String $apiKey
   */
  protected $apiKey;

  /**
   * Secure
   *
   * @var bool $secure;
   */
  protected $secure = false;

  /**
   * Curl
   *
   * @var bool $curl;
   */
  protected $curl = true;

  /**
   * Curl
   *
   * @var bool $curlAvailable;
   */
  protected $curlAvailable = true;

  /**
   * Ymlp constructor.
   *
   * @param string $api_key
   *   The Ymlp API key.
   * @param string $api_username
   *   The Ymlp API username.
   * @param bool $secure
   *   If connection need to be secure.
   */
  public function __construct($api_key, $api_username = 'apikey', $secure = false) {
	$this->apiKey = $api_key;
	$this->apiUsername = $api_username;
	$this->secure = $secure;
	$this->curlAvailable = function_exists( 'curl_init' ) && function_exists( 'curl_setopt' );
  }

  /**
   * Return the error message
   */
  public function getErrorMessage() {
	return $this->errorMessage;
  }

  /**
   * Return the api key
   */
  public function getApiKey() {
	return $this->apiKey;
  }

  /**
   * Return the api username
   */
  public function getApiUsername() {
	return $this->apiUsername;
  }

  /**
   * Return the secure status
   */
  public function getSecureStatus() {
	return $this->secure;
  }

  /**
   * Return the curl status
   */
  public function getCurlStatus() {
	return $this->curl;
  }

  /**
   * Return the curl available status
   */
  public function getCurlAvailableStatus() {
	return $this->curlAvailable;
  }

  /**
   * Set if the connection need to be secure.
   *
   * @param bool $val
   *   The logical bool value.
   */
  public function useSecure($val) {
	if ($val===true){
	  $this->secure = true;
	}
	else {
	  $this->secure = false;
	}
  }

  /**
   * Direct call to Ymlp using curl
   *
   * @param array $params
   *   All additional parameters
   */
  public function doCall($method = '',$params = array()) {
	$params["key"] = $this->apiKey;
	$params["username"] = $this->apiUsername;
	$params["output"] = "PHP";
	$this->errorMessage = "";

	if (!isset($postdata)) {$postdata = '';}
	foreach ( $params as $k => $v ) {
	  $postdata .= '&' . $k . '=' .rawurlencode(utf8_encode($v));

	  if ( $this->curl && $this->curlAvailable )  {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		if ($this->secure){
		  curl_setopt( $ch, CURLOPT_URL, "https://" .$this->apiUrl . $method );
		}
		else {
		  curl_setopt( $ch, CURLOPT_URL, "http://" .$this->apiUrl . $method );
		}

		$response = curl_exec( $ch );
		if(curl_errno($ch)) {
		  $this->errorMessage = curl_error($ch);
		  return false;
		}
	  }
	  else {
		$this->apiUrl = parse_url( "http://" .$this->apiUrl . $method);
		$payload = "POST " . $this->apiUrl["path"] . "?" . $this->apiUrl["query"] . " HTTP/1.0\r\n";
		$payload .= "Host: " . $this->apiUrl["host"] . "\r\n";
		$payload .= "User-Agent: YMLP_API\r\n";
		$payload .= "Content-type: application/x-www-form-urlencoded\r\n";
		$payload .= "Content-length: " . strlen($postdata) . "\r\n";
		$payload .= "Connection: close \r\n\r\n";
		$payload .= $postdata;

		ob_start();
		if ($this->secure){
		  $sock = fsockopen("ssl://".$this->apiUrl["host"], 443, $errno, $errstr);
		}
		else {
		  $sock = fsockopen($this->apiUrl["host"], 80, $errno, $errstr);
		}

		if(!$sock) {
		  $this->errorMessage = "ERROR $errno: $errstr";
		  ob_end_clean();
		  return false;
		}

		$response = "";
		fwrite($sock, $payload);
		while(!feof($sock)) {
		  $response .= fread($sock,8192);
		}
		fclose($sock);
		ob_end_clean();

		list($throw, $response) = explode("\r\n\r\n", $response, 2);
	  }
	}

	if(ini_get("magic_quotes_runtime")) $response = stripslashes($response);

	if (strtoupper($params["output"]) == "PHP" ) {
	  $serial = unserialize($response);
	  if ($response && $serial === false) {
		$this->errorMessage = "Bad Response: " . $response;
		return false;
	  }
	  else {
		$response = $serial;
	  }
	}

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function Ping() {
	return $this->doCall("Ping");
  }

  //------------------------------------------------------------
  // GROUPS [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function GroupsGetList() {
	return $this->doCall("Groups.GetList");
  }

  /**
   * {@inheritdoc}
   */
  public function GroupsAdd($GroupName = '') {
	$params = array();
	$params["GroupName"] = $GroupName;

	return $this->doCall("Groups.Add", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function GroupsDelete($GroupId = '') {
	$params = array();
	$params["GroupId"] = $GroupId;

	return $this->doCall("Groups.Delete", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function GroupsUpdate($GroupId = '', $GroupName = '') {
    $params = array();
    $params["GroupId"] = $GroupId;
    $params["GroupName"] = $GroupName;

	return $this->doCall("Groups.Update", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function GroupsEmpty($GroupId = '') {
	$params = array();
	$params["GroupId"] = $GroupId;

	return $this->doCall("Groups.Empty", $params);
  }
  //------------------------------------------------------------
  // GROUPS [end]
  //------------------------------------------------------------

  //------------------------------------------------------------
  // FIELDS [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function FieldsGetList() {
	return $this->doCall("Fields.GetList");
  }

  /**
   * {@inheritdoc}
   */
  public function FieldsAdd($FieldName = '', $Alias = '', $DefaultValue = '', $CorrectUppercase = '') {
	$params = array();
	$params["FieldName"] = $FieldName;
	$params["Alias"] = $Alias;
	$params["DefaultValue"] = $DefaultValue;
	$params["CorrectUppercase"] = $CorrectUppercase;

	return $this->doCall("Fields.Add", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function FieldsDelete($FieldId = '') {
	$params = array();
	$params["FieldId"] = $FieldId;

	return $this->doCall("Fields.Delete", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function FieldsUpdate($FieldId = '', $FieldName = '', $Alias = '', $DefaultValue = '', $CorrectUppercase = '') {
	$params = array();
	$params["FieldId"] = $FieldId;
	$params["FieldName"] = $FieldName;
	$params["Alias"] = $Alias;
	$params["DefaultValue"] = $DefaultValue;
	$params["CorrectUppercase"] = $CorrectUppercase;

	return $this->doCall("Fields.Update", $params);
  }
  //------------------------------------------------------------
  // FIELDS [end]
  //------------------------------------------------------------

  //------------------------------------------------------------
  // CONTACTS [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function ContactsAdd($Email = '', $OtherFields = '', $GroupID = '', $OverruleUnsubscribedBounced = '') {
	$params = array();
	$params["Email"] = $Email;
	if (!is_array($OtherFields)) $OtherFields=array();
	foreach ($OtherFields as $key=>$value) {
		$params[$key] = $value;
		}
	$params["GroupID"] = $GroupID;
	$params["OverruleUnsubscribedBounced"] = $OverruleUnsubscribedBounced;

	return $this->doCall("Contacts.Add", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsUnsubscribe($Email = '') {
	$params = array();
	$params["Email"] = $Email;

	return $this->doCall("Contacts.Unsubscribe", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsDelete($Email = '', $GroupID = '') {
	$params = array();
	$params["Email"] = $Email;
	$params["GroupID"] = $GroupID;

	return $this->doCall("Contacts.Delete", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsGetContact($Email = '') {
	$params = array();
	$params["Email"] = $Email;

	return $this->doCall("Contacts.GetContact", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsGetList($GroupID = '', $FieldID = '', $Page = '', $NumberPerPage = '', $StartDate = '', $StopDate = '') {
	$params = array();
	$params["GroupID"] = $GroupID;
	$params["FieldID"] = $FieldID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["StartDate"] = $StartDate;
	$params["StopDate"] = $StopDate;

	return $this->doCall("Contacts.GetList", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsGetUnsubscribed($FieldID = '', $Page = '', $NumberPerPage = '', $StartDate = '', $StopDate = '') {
	$params = array();
	$params["FieldID"] = $FieldID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["StartDate"] = $StartDate;
	$params["StopDate"] = $StopDate;

	return $this->doCall("Contacts.GetUnsubscribed", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsGetDeleted($FieldID = '', $Page = '', $NumberPerPage = '', $StartDate = '', $StopDate = '') {
	$params = array();
	$params["FieldID"] = $FieldID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["StartDate"] = $StartDate;
	$params["StopDate"] = $StopDate;

	return $this->doCall("Contacts.GetDeleted", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ContactsGetBounced($FieldID = '', $Page = '', $NumberPerPage = '', $StartDate = '', $StopDate = '') {
	$params = array();
	$params["FieldID"] = $FieldID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["StartDate"] = $StartDate;
	$params["StopDate"] = $StopDate;

	return $this->doCall("Contacts.GetBounced", $params);
  }
  //------------------------------------------------------------
  // CONTACTS [end]
  //------------------------------------------------------------

  //------------------------------------------------------------
  // FILTERS [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function FiltersGetList() {
	return $this->doCall("Filters.GetList");
  }

  /**
   * {@inheritdoc}
   */
  public function FiltersAdd($FilterName = '', $Field = '', $Operand = '', $Value = '') {
	$params = array();
	$params["FilterName"] = $FilterName;
	$params["Field"] = $Field;
	$params["Operand"] = $Operand;
	$params["Value"] = $Value;

	return $this->doCall("Filters.Add", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function FiltersDelete($FilterId = '') {
	$params = array();
	$params["FilterId"] = $FilterId;

	return $this->doCall("Filters.Delete", $params);
  }
  //------------------------------------------------------------
  // FILTERS [end]
  //------------------------------------------------------------

  //------------------------------------------------------------
  // ARCHIVE [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetList($Page = '', $NumberPerPage = '', $StartDate = '', $StopDate = '', $Sorting = '', $ShowTestMessages = '') {
	$params = array();
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["StartDate"] = $StartDate;
	$params["StopDate"] = $StopDate;
	$params["Sorting"] = $Sorting;
	$params["ShowTestMessages"] = $ShowTestMessages;

	return $this->doCall("Archive.GetList", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetSummary($NewsletterID = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;

	return $this->doCall("Archive.GetSummary", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetContent($NewsletterID = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;

	return $this->doCall("Archive.GetContent", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetRecipients($NewsletterID = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetRecipients", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetDelivered($NewsletterID = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetDelivered", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetBounces($NewsletterID = '', $ShowHardBounces = '', $ShowSoftBounces = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["ShowHardBounces"] = $ShowHardBounces;
	$params["ShowSoftBounces"] = $ShowSoftBounces;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetBounces", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetOpens($NewsletterID = '', $UniqueOpens = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["UniqueOpens"] = $UniqueOpens;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetOpens", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetUnopened($NewsletterID = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetUnopened", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetTrackedLinks($NewsletterID = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;

	return $this->doCall("Archive.GetTrackedLinks", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetClicks($NewsletterID = '', $LinkID = '', $UniqueClicks = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["LinkID"] = $LinkID;
	$params["UniqueClicks"] = $UniqueClicks;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetClicks", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function ArchiveGetForwards($NewsletterID = '', $Page = '', $NumberPerPage = '', $Sorting = '') {
	$params = array();
	$params["NewsletterID"] = $NewsletterID;
	$params["Page"] = $Page;
	$params["NumberPerPage"] = $NumberPerPage;
	$params["Sorting"] = $Sorting;

	return $this->doCall("Archive.GetForwards", $params);
  }
  //------------------------------------------------------------
  // ARCHIVE [end]
  //------------------------------------------------------------


  //------------------------------------------------------------
  // NEWSLETTER [begin]
  //------------------------------------------------------------

  /**
   * {@inheritdoc}
   */
  public function NewsletterGetFroms() {
	return $this->doCall("Newsletter.GetFroms");
  }

  /**
   * {@inheritdoc}
   */
  public function NewsletterAddFrom($FromEmail = '', $FromName = '') {
	$params = array();
	$params["FromEmail"] = $FromEmail;
	$params["FromName"] = $FromName;

	return $this->doCall("Newsletter.AddFrom", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function NewsletterDeleteFrom($FromID = '') {
	$params = array();
	$params["FromID"] = $FromID;

	return $this->doCall("Newsletter.DeleteFrom", $params);
  }

  /**
   * {@inheritdoc}
   */
  public function NewsletterSend($Subject = '', $HTML = '', $Text = '', $DeliveryTime = '',
							$FromID = '', $TrackOpens = '', $TrackClicks = '', $TestMessage = '',
							$GroupID = '', $FilterID = '', $CombineFilters = '') {
	$params = array();
	$params["Subject"] = $Subject;
	$params["HTML"] = $HTML;
	$params["Text"] = $Text;
	$params["DeliveryTime"] = $DeliveryTime;
	$params["FromID"] = $FromID;
	$params["TrackOpens"] = $TrackOpens;
	$params["TrackClicks"] = $TrackClicks;
	$params["TestMessage"] = $TestMessage;
	$params["GroupID"] = $GroupID;
	$params["FilterID"] = $FilterID;
	$params["CombineFilters"] = $CombineFilters;

	return $this->doCall("Newsletter.Send", $params);
  }
  //------------------------------------------------------------
  // NEWSLETTER [end]
  //------------------------------------------------------------
}
