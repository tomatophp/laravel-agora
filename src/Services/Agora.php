<?php

namespace TomatoPHP\LaravelAgora\Services;

use Exception;
use TomatoPHP\LaravelAgora\Services\Token\RtcTokenBuilder;

/**
 *
 */
class Agora
{
    /**
     * @var string|int
     */
    protected string|int $id;
    /**
     * @var string|null
     */
    protected string|null $channel="agora";
    /**
     * @var string|null
     */
    protected string|null $uId = "";
    /**
     * @var bool
     */
    protected bool $audio=false;
    /**
     * @var bool
     */
    protected bool $join = false;
    /**
     * @var int|null
     */
    protected int|null $expireIn = null;

    /**
     * @param string|int $id
     * @return static
     */
    public static function make(string|int $id): static
    {
        return (new self())->id($id);
    }

    /**
     * @param string|int $id
     * @return $this
     */
    public function id(string|int $id): static
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param bool|null $join
     * @return $this
     */
    public function join(bool|null $join= true): static
    {
        $this->join = $join;
        return $this;
    }

    /**
     * @param bool|null $audio
     * @return $this
     */
    public function audioOnly(bool|null $audio=true): static
    {
        $this->audio = $audio;
        return $this;
    }

    /**
     * @param string $channel
     * @return $this
     */
    public function channel(string $channel): static
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @param string $uId
     * @return $this
     */
    public function uId(string $uId): static
    {
        $this->uId = $uId;
        return $this;
    }

    /**
     * Set how long (in seconds) the generated token stays valid.
     * Defaults to the `laravel-agora.agora.expire` config value (3600 seconds).
     *
     * @param int $seconds
     * @return $this
     */
    public function expireIn(int $seconds): static
    {
        if ($seconds < 1) {
            throw new \InvalidArgumentException('Agora token expiry must be at least 1 second.');
        }

        $this->expireIn = $seconds;
        return $this;
    }

    /**
     * @return int
     */
    public function getExpireIn(): int
    {
        return $this->expireIn ?? max(1, (int) config('laravel-agora.agora.expire', 3600));
    }

    /**
     * @return string|void|null
     */
    public function token()
    {
        $appID = config('laravel-agora.agora.app_id');
        $appCertificate = config('laravel-agora.agora.app_certificate');

        if($appID && $appCertificate){
            $channelName = $this->channel . '.'. $this->id;
            if ($this->join) {
                $role = RtcTokenBuilder::$roles['RoleSubscriber'];
            } else {
                $role = RtcTokenBuilder::$roles['RolePublisher'];
            }

            //Build a Time
            $expireTimeInSeconds = $this->getExpireIn();
            $currentTimestamp = now()->getTimestamp();
            $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;


            $token = RtcTokenBuilder::build(
                appID: $appID,
                appCertificate: $appCertificate,
                channelName: $channelName,
                uid: $this->uId,
                role: $role,
                privilegeExpireTs: $privilegeExpiredTs,
                type: $this->audio ? 'audio' : 'video'
            );

            return $token;
        }

        abort(400, 'Sorry Agora API Key, or Certificate not Exists');
    }
}
