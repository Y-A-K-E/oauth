<?php
/**
 * Created by PhpStorm.
 * User: medivh
 * Date: 18-4-16
 * Time: 下午4:20
 */

namespace medivh\OAuth\Driver;


use GuzzleHttp\Client;
use medivh\OAuth\ConfigNotFound;
use medivh\OAuth\OAuthInterface;
use medivh\OAuth\UserInfo\BaiDuUserInfo;
use medivh\OAuth\UserInfo\UserInfo;

class BaiDu implements OAuthInterface {

    protected $authorizeUri = 'https://openapi.baidu.com/oauth/2.0/authorize';

    protected $AccessTokenURL = 'https://openapi.baidu.com/oauth/2.0/token';

    protected $userInfoUri = 'https://openapi.baidu.com/rest/2.0/passport/users/getInfo';
	
	protected $userInfoUriWithPan = 'https://pan.baidu.com/rest/2.0/xpan/nas';

    protected $config = [];

    protected $openId = '';

    protected $accessToken = '';

    protected $CSRFToken = '';

    /**
     * @var array
     */
    protected $accessTokenInfo = [];

    public function getOtherAuthorizeInfo(): string {
        return '';
    }

    public function getAuthorizeGateway(): string {
        return $this->authorizeUri;
    }

    public function getAuthorizeConfig(): array {
        return [
            'response_type' => 'code',
            'client_id' => $this->getConfig('appid'),
            'redirect_uri' => $this->getConfig('redirect_uri'),
            'display' => $this->getConfig('display'),
			'qrcode'=> empty($this->getConfig('qrcode')) ? 0:1 ,
			'qrloginfrom'=> empty($this->getConfig('qrloginfrom'))? $this->getConfig('display') : $this->getConfig('qrloginfrom') ,
			'confirm_login'=> empty($this->getConfig('confirm_login')) ? 0:1 ,
			'scope'=> empty($this->getConfig('scope')) ?  '' : $this->getConfig('scope') 
        ];
    }

    public function setConfig(array $config): OAuthInterface {
        $this->config = $config;
        return $this;
    }

    public function getConfig(string $key): string {
        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }else{
            return '';
        }

        //throw new ConfigNotFound("配置参数{$key}未找到");
    }

    public function getAccessTokenUri(): string {
        return $this->AccessTokenURL;
    }

    public function getRefreshTokenUri(): string {
        return '';
    }

    public function getAuthAccessTokenUri(): string {
        return '';
    }

    public function getRefreshTokenMethod(): string {
        return 'GET';
    }

    public function getRefreshTokenParams(string $refreshToken): array {
        return [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => $this->getConfig('appid'),
            'client_secret' => $this->getConfig('secret'),
        ];
    }

    public function getAccessTokenMethod(): string {
        return 'GET';
    }

    public function getUserInfoMethod(): string {
        return 'GET';
    }

    public function getUserInfoUri(): string {
        return $this->userInfoUri;
    }

    public function getUserInfoParams(): array {
        return [
			'access_token' => $this->getAccessToken(),
			'get_unionid' => empty($this->getConfig('get_unionid')) ? 0:1 ,
		];
    }

    public function setAccessTokenInfo(string $accessTokenInfo): OAuthInterface {
        $this->accessTokenInfo = json_decode($accessTokenInfo, true);
        $this->accessToken = $this->accessTokenInfo['access_token'];
        return $this;
    }

    public function getAccessTokenParams(): array {
        return [
            'grant_type' => 'authorization_code',
            'code' => $_GET['code'],
            'client_id' => $this->getConfig('appid'),
            'client_secret' => $this->getConfig('secret'),
            'redirect_uri' => $this->getConfig('redirect_uri'),
        ];
    }

    public function generateUserInfo(string $response): UserInfo {
        $bdUserInfo = BaiDuUserInfo::decode(json_decode($response, true));
        $this->setOpenId($bdUserInfo->openId);

		//多一个步骤获取隐藏的用户名,如果获取的用户名中有星号,表示用户名就是部分隐藏的,用百度网盘接口获取用户全名
		if (strpos($bdUserInfo->nickname, '*') !== false) {
            $params = ['access_token' => $this->getAccessToken(),'method'=>'uinfo'];
            $client = new Client;
            $response = $client->request('GET', $this->userInfoUriWithPan, ['query' => $params]);
            $responseContents = json_decode($response->getBody()->getContents(), true);
			if ($responseContents['errno'] == 0){
				$bdUserInfo->nickname = $responseContents['baidu_name'];
			}  
		} 

        return $bdUserInfo;
    }

    public function getOpenId(): string {
        if ( empty($this->openId) ){
            $params = ['access_token' => $this->getAccessToken()];
            $client = new Client;
            $response = $client->request('GET', $this->userInfoUri, ['query' => $params]);
            $responseContents = json_decode($response->getBody()->getContents(), true);

            $this->openId = $responseContents['openid'];
        }
        exit;
    }

    public function setOpenId(string $openId): OAuthInterface {
        $this->openId = $openId;
        return $this;
    }

    public function setAccessToken(string $accessToken): OAuthInterface {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getAccessToken(): string {
        return $this->accessToken;
    }

    public function getCSRFToken(): string {
        return '';
    }

    public function getAccessTokenInfo(string $key = ''): string {
        return strval($this->accessTokenInfo[$key]);
    }


}