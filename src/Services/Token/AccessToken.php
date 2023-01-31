<?php
namespace TomatoPHP\LaravelAgora\Services\Token;

class AccessToken
{
    /**
     * @var array|int[]
     */
    public static array $privileges = array(
        "kJoinChannel" => 1,
        "kPublishAudioStream" => 2,
        "kPublishVideoStream" => 3,
        "kPublishDataStream" => 4,
        "kRtmLogin" => 1000,
    );

    /**
     * @var string
     */
    private string $appID;

    /**
     * @var string
     */
    private string $appCertificate;

    /**
     * @var string
     */
    private string $channelName;

    /**
     * @var string
     */
    private string $uid;

    /**
     * @var Message
     */
    private Message $message;

    /**
     * @return void
     */
    function __construct()
    {
        $this->message = new Message();
    }

    /**
     * @param string $uid
     * @return void
     */
    function setUid(string $uid): void
    {
        if ($uid === 0) {
            $this->uid = "";
        } else {
            $this->uid = $uid . '';
        }
    }

    /**
     * @param string $name
     * @param string|null $str
     * @return bool
     */
    function is_nonempty_string(string $name,string|null $str): bool
    {
        if (is_string($str) && $str !== "") {
            return true;
        }
        echo $name . " check failed, should be a non-empty string";
        return false;
    }

    /**
     * @param string $appID
     * @param string $appCertificate
     * @param string $channelName
     * @param string $uid
     * @return static|null
     */
    static function init(string $appID,string $appCertificate,string $channelName,string $uid): static|null
    {
        $accessToken = new static();

        if (!$accessToken->is_nonempty_string("appID", $appID) ||
            !$accessToken->is_nonempty_string("appCertificate", $appCertificate) ||
            !$accessToken->is_nonempty_string("channelName", $channelName)) {
            return null;
        }

        $accessToken->appID = $appID;
        $accessToken->appCertificate = $appCertificate;
        $accessToken->channelName = $channelName;

        $accessToken->setUid($uid);
        $accessToken->message = new Message();
        return $accessToken;
    }

    /**
     * @param string $token
     * @param string $appCertificate
     * @param string $channel
     * @param string $uid
     * @return static|null
     */
    static function initWithToken(string $token,string $appCertificate,string $channel,string $uid): static|null
    {
        $accessToken = new static();
        if (!$accessToken->extract($token, $appCertificate, $channel, $uid)) {
            return null;
        }
        return $accessToken;
    }

    /**
     * @param string $key
     * @param int $expireTimestamp
     * @return $this
     */
    function addPrivilege(string $key,int $expireTimestamp): static
    {
        $this->message->privileges[$key] = $expireTimestamp;
        return $this;
    }

    /**
     * @param string $token
     * @param string $appCertificate
     * @param string $channelName
     * @param string $uid
     * @return bool
     */
    function extract(string $token,string $appCertificate,string $channelName,string $uid): bool
    {
        $ver_len = 3;
        $appid_len = 32;
        $version = substr($token, 0, $ver_len);
        if ($version !== "006") {
            echo 'invalid version ' . $version;
            return false;
        }

        if (!$this->is_nonempty_string("token", $token) ||
            !$this->is_nonempty_string("appCertificate", $appCertificate) ||
            !$this->is_nonempty_string("channelName", $channelName)) {
            return false;
        }

        $appid = substr($token, $ver_len, $appid_len);
        $content = (base64_decode(substr($token, $ver_len + $appid_len, strlen($token) - ($ver_len + $appid_len))));

        $pos = 0;
        $len = unpack("v", $content . substr($pos, 2))[1];
        $pos += 2;
        $sig = substr($content, $pos, $len);
        $pos += $len;
        $crc_channel = unpack("V", substr($content, $pos, 4))[1];
        $pos += 4;
        $crc_uid = unpack("V", substr($content, $pos, 4))[1];
        $pos += 4;
        $msgLen = unpack("v", substr($content, $pos, 2))[1];
        $pos += 2;
        $msg = substr($content, $pos, $msgLen);

        $this->appID = $appid;
        $message = new Message();
        $message->unpackContent($msg);
        $this->message = $message;

        //non reversable values
        $this->appCertificate = $appCertificate;
        $this->channelName = $channelName;
        $this->setUid($uid);
        return true;
    }

    /**
     * @return string
     */
    function build(): string
    {
        $msg = $this->message->packContent();
        $val = array_merge(
            unpack("C*", $this->appID),
            unpack("C*", $this->channelName),
            unpack("C*", $this->uid),
            $msg
        );

        $sig = hash_hmac('sha256', implode(array_map("chr", $val)), $this->appCertificate, true);

        $crc_channel_name = crc32($this->channelName) & 0xffffffff;
        $crc_uid = crc32($this->uid) & 0xffffffff;

        $content = array_merge(
            unpack("C*", $this->packString($sig)),
            unpack("C*", pack("V", $crc_channel_name)),
            unpack("C*", pack("V", $crc_uid)),
            unpack("C*", pack("v", count($msg))),
            $msg
        );
        $version = "006";
        $ret = $version . $this->appID . base64_encode(implode(array_map("chr", $content)));
        return $ret;
    }

    /**
     * @param $value
     * @return string
     */
    function packString($value): string
    {
        return pack("v", strlen($value)) . $value;
    }

}
