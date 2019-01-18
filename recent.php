<?php
/**
 * Copyright (c) 2014-present, Facebook, Inc. All rights reserved.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

// Configurations
use FacebookAds\Api;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;
use FacebookAds\Object\Campaign;
use FacebookAds\Object\Fields\CampaignFields;
use FacebookAds\Object\Values\AdAccountTargetingUnifiedObjectiveValues;
use FacebookAds\Object\AdSet;
use FacebookAds\Object\AdCreative;
use FacebookAds\Object\AdCreativeLinkData;
use FacebookAds\Object\Fields\AdCreativeLinkDataFields;
use FacebookAds\Object\AdCreativeObjectStorySpec;
use FacebookAds\Object\Fields\AdCreativeObjectStorySpecFields;
use FacebookAds\Object\Fields\AdCreativeFields;
use FacebookAds\Object\Ad;
use FacebookAds\Object\Fields\AdFields;
use FacebookAds\Object\Fields\AdSetFields;
use FacebookAds\Object\AdImage;
use FacebookAds\Object\Fields\AdImageFields;

try {

	$access_token = '<ACCESS TOKEN>';
	$app_id = '<APP ID>';
	$app_secret = '<APP SECRET>';
	// should begin with "act_" (eg: $account_id = 'act_1234567890';)
	$account_id = 'act_<ACCOUNT ID>';
	define('SDK_DIR', __DIR__ . '/facebook-php-ads-sdk'); // Path to the SDK directory
	$loader = include SDK_DIR.'/vendor/autoload.php';
	date_default_timezone_set('America/Los_Angeles');
	// Configurations - End

	if(is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
		throw new \Exception(
			'You must set your access token, app id and app secret before executing'
		);
	}

	if (is_null($account_id)) {
		throw new \Exception(
			'You must set your account id before executing');
	}



	Api::init($app_id, $app_secret, $access_token);


	/**
	 * Step 1 Read the AdAccount (optional)
	 */


	$account = (new AdAccount($account_id))->read(array(
		AdAccountFields::ID,
		AdAccountFields::NAME,
		AdAccountFields::ACCOUNT_STATUS,
	));

	echo "\nUsing this account: ";
	echo $account->id."\n";

	// Check the account is active
	if($account->{AdAccountFields::ACCOUNT_STATUS} !== 1) {
		throw new \Exception(
			'This account is not active');
	}

	/**
	 * Step 2 Create the Campaign
	 */

	$campaign  = new Campaign(null, $account->id);
	$campaign->setData(array(
		CampaignFields::NAME => 'My First Campaign',
		CampaignFields::OBJECTIVE => AdAccountTargetingUnifiedObjectiveValues::LINK_CLICKS,
	));

	$campaign->validate()->create(array(
		Campaign::STATUS_PARAM_NAME => Campaign::STATUS_PAUSED,
	));
	echo "Campaign ID:" . $campaign->id . "\n";


	/**
	 * Step 4 Create the AdSet
	 */

	$targettingArray = array();
	//country targeting
	$targettingArray['countries'] = array('US');
	$targeting->{'geo_locations'} = $targettingArray;

	// gender targetting
	$targeting->{'genders'} = array(1);

	// device targetting
	$targeting->{'device_platforms'} = array('desktop');

	// publisher platform targeting and facebook platform targeting

	$targeting->{'publisher_platforms'} =  array('facebook');
	$targeting->{'facebook_positions'} =  array('feed');

	echo '<pre>'; print_r($targeting);

	$adSetData = array();

	$startDate = '01-02-2019';
	$startTime = '00:00';
	$startDateTime = trim($startDate) . " " . trim($startTime);
	$adSetData[AdSetFields::START_TIME] = strtotime($startDateTime);

	$endDate = '03-02-2019';
	$endTime = '00-00';
	$endDateTime = trim($endDate) . " " . trim($endTime);

	$adSetData[AdSetFields::DAILY_BUDGET] = '50000';
	$adSetData['bid_amount'] = '1000';

	$adSetData[AdSetFields::NAME] = 'test campaign_18';
	$adSetData[AdSetFields::OPTIMIZATION_GOAL] = FacebookAds\Object\Values\AdSetOptimizationGoalValues::LINK_CLICKS;
	$adSetData[AdSetFields::BILLING_EVENT] = FacebookAds\Object\Values\AdSetBillingEventValues::LINK_CLICKS;
	$adSetData[AdSetFields::CAMPAIGN_ID] = $campaign->id;
	$adSetData[AdSetFields::TARGETING] = $targeting;
	$adSetData[AdSetFields::STATUS] = AdSet::STATUS_ACTIVE;

	$adset = new AdSet();
	$adset->setParentId($account->id);
	$adSetAPIObject = $adset->create($adSetData);

	echo 'AdSet  ID: '. print_r($adSetAPIObject->{AdSetFields::ID}) . "\n";

	$image = new AdImage(null, $account->id);
	$image->{AdImageFields::FILENAME}
	       = SDK_DIR.'/test/misc/image.png';
	$image->create();
	echo 'Image Hash: '.$image->hash . "\n";

	$link_data = new AdCreativeLinkData();
	$link_data->setData(array(
		AdCreativeLinkDataFields::MESSAGE => 'try it out',
		AdCreativeLinkDataFields::LINK => 'www.google.com',
		AdCreativeLinkDataFields::IMAGE_HASH => $image->hash,
	));

	$object_story_spec = new AdCreativeObjectStorySpec();
	$object_story_spec->setData(array(
		AdCreativeObjectStorySpecFields::PAGE_ID => '<PAGE ID>',
		AdCreativeObjectStorySpecFields::LINK_DATA => $link_data,
	));

	$creative = new AdCreative(null, $account->id);

	$creative->setData(array(
		AdCreativeFields::NAME => 'Sample Creative',
		AdCreativeFields::OBJECT_STORY_SPEC => $object_story_spec,
	));


	$creative->create();
	echo 'Creative ID: '.$creative->id . "\n";


	/**
	 * Step 7 Create an Ad
	 */

	$exceptionArray = array();
	echo 'account Id: '.$account->id."\n";



	$ad = new Ad(null, $account->id);
	$ad->setData(array(
		AdFields::NAME => 'My Ad',
		AdFields::ADSET_ID => $adSetAPIObject->{AdSetFields::ID},
		AdFields::CREATIVE => array('creative_id' => $creative->id),
	));
	$ad->create(array(
		Ad::STATUS_PARAM_NAME => Ad::STATUS_PAUSED,
	));


	echo 'Ad ID:' . $ad->id . "\n";

}catch (FacebookAds\Exception\Exception $fx) {
	//my_var_dump($fx, 'Facebook Exception: ' . __FUNCTION__);
	$exceptionArray = array();

	if (method_exists($fx, 'getErrorUserTitle')) {
		array_push($exceptionArray, $fx->getErrorUserTitle());
	} else if (method_exists($fx, 'getErrorUserMessage')) {
		array_push($exceptionArray, $fx->getErrorUserMessage());
	}

	array_push($exceptionArray, $fx->getMessage());

	$mssage = implode(', ', $exceptionArray);
	print_r($mssage, $fx->getCode());
} catch (Exception $ex) {
	//my_var_dump($ex, 'Exception: ' . __FUNCTION__);
	print_r($ex->getMessage(), $ex->getCode());
}
echo '<pre>'; print_r($exceptionArray);