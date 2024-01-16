<?php
/**
 * Created by PhpStorm.
 * User: medivh
 * Date: 18-4-16
 * Time: 下午5:07
 */

namespace medivh\OAuth\UserInfo;


class BaiDuUserInfo extends UserInfo {
    public static function decode(array $data): UserInfoInterface {
        $user = new BaiDuUserInfo;

        $baseAvatarUri = 'https://himg.bdimg.com/sys/portrait/item/%s.jpg';
/**
下面数据是2024年1月获取
{
  "portrait": "c45d4643e69d80e6898b6c06",
  "username": "F***手",
  "is_bind_mobile": "1",
  "is_realname": "1",
  "birthday": "2013-01-01",
  "sex": "1",
  "openid": "o2VgP7Ujk1rVjJ-8zxTJAQhex03gQBC",
  "unionid": "ulmAqjHyX1lTL3bRdwmFfIyDlKwLp0S"
}
 */

        $user->avatar = !array_key_exists('portrait', $data) ? '' : sprintf($baseAvatarUri, $data['portrait']);
        $user->channel = 'baidu';
        $user->gender = !array_key_exists('sex', $data) ? '' : $data['sex'];;
        $user->nickname = !array_key_exists('username', $data) ? '' : $data['username'];
        $user->openId = !array_key_exists('openid', $data) ? '' : $data['openid'];
        $user->unionId = !array_key_exists('unionid', $data) ? '' : $data['unionid'];

        return $user;
    }
}