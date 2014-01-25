<?php
/**
 * Estimating the Facebook acquiarice of fan pag
 * Facebook Access Token with read_stream permission is required.
 *
 * @author james jara @ www.jamesara.com
 
	Redistribution and use in source and binary forms, with or without
	modification, are permitted provided that the following conditions are met:
		* Redistributions of source code must retain the above copyright
		  notice, this list of conditions and the following disclaimer.
		* Redistributions in binary form must reproduce the above copyright
		  notice, this list of conditions and the following disclaimer in the
		  documentation and/or other materials provided with the distribution.
		* Neither the name of the author nor the names of its contributors 
		  may be used to endorse or promote products derived from this software 
		  without specific prior written permission.
 
	THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS "AS IS" AND ANY 
	EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
	WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
	DISCLAIMED. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, 
	INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT 
	LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, 
	OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
	LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
	OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF 
	ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

 */

class FacebookQuickFql {
	private $fb = 'https://graph.facebook.com/';
	private $accessToken;
	private $targetId;
	private $data;
	function __construct() {  }
	public function doCurlFql($fql , $Nonsql = false) {
		if ( $Nonsql === true )
			$url = $this->fb . urlencode($fql);
		else
			$url = $this->fb . 'fql?q=' . urlencode($fql). '&access_token=' . $this->accessToken;
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$decodedResult = json_decode(curl_exec($ch), true);	
		curl_close($ch);
		$result = array();
		if  ( $Nonsql === true )
			$result = $decodedResult;
		else if( isset($decodedResult['data']))
			$result = $decodedResult['data'];
		else {
			throw new Exception("Facebook FQL Error.access token is valid?".@implode(",",$decodedResult));
		}
		return $result;
	} 
	public function getFriendsCount(){ 
		if( $this->data == null ) $this->fetchData();
		if( array_key_exists('friend_count',$this->data)) return $this->data['friend_count']; else return null ;
	}
	public function getAttribute($key){ 
		if( $this->data == null ) $this->fetchData();
		if( array_key_exists($key,$this->data)) return $this->data[$key]; else return null ;
	}
	public function getIdFromUsername($username){ 
		$fql = $username;
		$result = $this->doCurlFql($fql, true );
		if( @$result['id'] != null )
			return $result['id'];
		else
			return ;
	}
	public function fetchData() { 
		$fql = " SELECT  friend_count, about_me, birthday_date, books, music, movies, tv, third_party_id, subscriber_count, significant_other_id, relationship_status, pic_big, pic_cover, profile_url, meeting_sex, likes_count, friend_request_count, interests, inspirational_people, hometown_location, email, current_address, devices, education, current_location, allowed_restrictions,age_range, affiliations, activities, uid, name, username, locale FROM user WHERE uid =  ".$this->targetId;
		$result = $this->doCurlFql($fql);
		$this->data = $result[0];
	}
	public function flush() {  
		$this->data = null  ;
	}
	public function setAccessToken($access_token){ 
		$this->accessToken  = $access_token;
	}
	public function setTargetId($user_id){ 
		$this->targetId  = $user_id;
	}
}

class FacebookScraper {
	private function doCurl($url) {
		$ch = curl_init($url);
		$headers = array(
		"Accept-Language: en-us",
		"User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.1.4322; .NET CLR 2.0.50727; .NET CLR 3.0.04506.30)",
		"Connection: Keep-Alive",
		"Cache-Control: no-cache"
		);
		$referer = 'http://www.google.com/search?q='.$url;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); //emulate browser
		curl_setopt($ch, CURLOPT_REFERER, $referer);	//spoof refer xd
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		return curl_exec($ch);
	} 
	public function extractIds(  $data ){  
		$regex = '#https?\://(?:www\.)?facebook\.com/(\d+|[A-Za-z0-9\.]+)/?#';
		preg_match_all($regex, $data , $matches); $matcheskeys = array();
		foreach ($matches[1] as $m) array_push( $matcheskeys, $m );  
		return array_unique($matcheskeys, SORT_REGULAR);
	}
	public function extractFanPagesFromUrl( $url ){  
		$rawr_html = $this->doCurl( $url.'/likes' );
		$regex = '#<span class="visible">([\s\S]*?)</span>#'; //get likes container 
		preg_match($regex, $rawr_html , $matchdiv);
		if(@$matchdiv[0]==null) return;
		$fanpages = $this->extractIds( $matchdiv[0]);
		return $fanpages;
	}
	public function extractFriendsFromUrl( $url ){ } 
	public function extractXXXXXXXFromUrl( $url ){ } 	
}
