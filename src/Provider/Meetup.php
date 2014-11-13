<?php namespace Howlowck\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Entity\User;

class Meetup extends AbstractProvider {
    public $scopes = array();
    public $responseType = 'json';

    public function urlAuthorize()
    {
        return 'https://secure.meetup.com/oauth2/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://secure.meetup.com/oauth2/access';
    }

    public function urlUserDetails(AccessToken $token)
    {
        return 'https://api.meetup.com/2/members?member_id=self&access_token='.$token;
    }

    public function userDetails($response, AccessToken $token)
    {
        if ( ! isset($response->results[0])) {
            return null;
        }

        $result = $response->results[0];
        $user = new User;


        $name = (isset($result->name)) ? $result->name : null;
        $description = (isset($result->bio)) ? $result->bio : null;
        $imageUrl = (isset($result->photo->photo_link)) ? $result->photo->photo_link : null;
        $meetupLink = (isset($result->link)) ? $result->link : null;

        $user->exchangeArray(array(
            'uid' => $result->id,
            'name' => $name,
            'description' => $description,
            'imageurl' => $imageUrl,
            'urls' => array( 'Meetup' => $meetupLink ),
        ));

        return $user;
    }

    public function userUid($response, AccessToken $token)
    {
        return $response->results[0]->id;
    }

    public function userEmail($response, AccessToken $token)
    {
        //meetup does not give away user's email
        return null;
    }

    public function userScreenName($response, AccessToken $token)
    {
        $result = $response->results[0];
        return (isset($result->name)) ? $result->name: $result->id;
    }
}