<?php namespace Slyvent\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Tool\BearerAuthorizationTrait;
use Psr\Http\Message\ResponseInterface;

class Meetup extends AbstractProvider
{
    use BearerAuthorizationTrait;

    /**
     * @var string Key used in the access token response to identify the resource owner.
     */
    const ACCESS_TOKEN_RESOURCE_OWNER_ID = 'id';
    /**
     * Meetup OAUTH URL.
     *
     * @const string
     */
    const OAUTH_MEETUP_URL = "https://secure.meetup.com/";

    /**
     * Meetup API URL.
     *
     * @const string
     */
    const API_MEETUP_URL = "https://api.meetup.com/";

    /**
     * Replaces the one hour expiry time from oauth2 tokens with a limit of up to two weeks
     *
     * @const string
     */
    const SCOPE_AGELESS = "ageless";
    /**
     * Access to basic Meetup group info and creating and editing Events and RSVP's, posting photos in version 2 API's and below
     *
     * @const string
     */
    const SCOPE_BASIC = "basic";
    /**
     * Allows the authorized application to create and make modifications to events in your Meetup groups on your behalf
     *
     * @const string
     */
    const SCOPE_EVENT_MANAGEMENT = "event_management";
    /**
     * Allows the authorized application to edit the settings of groups you organize on your behalf
     *
     * @const string
     */
    const SCOPE_GROUP_EDIT = "group_edit";
    /**
     * Allows the authorized application to create, modify and delete group content on your behalf
     *
     * @const string
     */
    const SCOPE_GROUP_CONTENT_EDIT = "group_content_edit";
    /**
     * Allows the authorized application to join new Meetup groups on your behalf
     *
     * @const string
     */
    const SCOPE_GROUP_JOIN = "group_join";
    /**
     * Enables Member to Member messaging (this is now deprecated)
     *
     * @const string
     */
    const SCOPE_MESSAGING = "messaging";
    /**
     * Allows the authorized application to edit your profile information on your behalf
     *
     * @const string
     */
    const SCOPE_PROFILE_EDIT = "profile_edit";
    /**
     * Allows the authorized application to block and unblock other members and submit abuse reports on your behalf
     *
     * @const string
     */
    const SCOPE_REPORTING = "reporting";
    /**
     * Allows the authorized application to RSVP you to events on your behalf
     *
     * @const string
     */
    const SCOPE_RSVP = "rsvp";

    public function getBaseAuthorizationUrl()
    {
        return self::OAUTH_MEETUP_URL . 'oauth2/authorize';
    }

    public function getBaseAccessTokenUrl(array $params)
    {
        return self::OAUTH_MEETUP_URL . 'oauth2/access';
    }

    public function getDefaultScopes()
    {
        return [self::SCOPE_BASIC];
    }

    public function getResourceOwnerDetailsUrl(AccessToken $token)
    {
        return self::API_MEETUP_URL . 'members/self';
    }

    protected function checkResponse(ResponseInterface $response, $data)
    {
        if (!empty($data['error'])) {
            $code  = 0;
            $error = $data['error'];

            if (is_array($error)) {
                $code  = $error['code'];
                $error = $error['message'];
            }

            throw new IdentityProviderException($error, $code, $data);
        }
    }

    protected function createResourceOwner(array $response, AccessToken $token)
    {
        return new MeetupUser($response);
    }
}