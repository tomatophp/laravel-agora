<?php

namespace TomatoPHP\LaravelAgora\Services\Token;

class RtcTokenBuilder
{
    public static array $roles = [
        "RoleAttendee" => 0,
        "RolePublisher" => 1,
        "RoleSubscriber" => 2,
        "RoleAdmin" => 101,
    ];

    /**
     * @param string $appID
     * @param string $appCertificate
     * @param string $channelName
     * @param string $uid
     * @param string $role
     * @param int|float $privilegeExpireTs
     * @param string|null $type
     * @return string|null
     */
    public static function build(
        string $appID,
        string $appCertificate,
        string $channelName,
        string $uid,
        string $role,
        int|float $privilegeExpireTs,
        string|null $type='video'
    ): string|null
    {

        //Generate Token
        $token = AccessToken::init($appID, $appCertificate, $channelName, $uid);

        //Get Token Privileges
        $privileges = AccessToken::$privileges;

        if($token !== null){
            //Add Join Channel Privilege
            $token->addPrivilege($privileges["kJoinChannel"], $privilegeExpireTs);

            if(
                ($role === self::$roles['RoleAttendee']) ||
                ($role === self::$roles['RolePublisher']) ||
                ($role === self::$roles['RoleAdmin']))
            {
                $type === 'video' ?: $token->addPrivilege($privileges["kPublishVideoStream"], $privilegeExpireTs);
                $token->addPrivilege($privileges["kPublishAudioStream"], $privilegeExpireTs);
                $token->addPrivilege($privileges["kPublishDataStream"], $privilegeExpireTs);
            }

            return $token->build();
        }

        return null;
    }
}
