<?php namespace Slyvent\OAuth2\Client\Test\Provider;

use Slyvent\OAuth2\Client\Provider\Meetup;
use \Mockery as m;

class MeetupTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Slyvent\OAuth2\Client\Provider\Meetup|null
     */
    protected $provider;

    protected function setUp()
    {
        $this->provider = new Meetup([
            'clientId'     => 'mock',
            'clientSecret' => 'mock_secret',
            'redirectUri'  => 'none',
        ]);
    }

    public function testAuthorizationUrl()
    {
        $url = $this->provider->getAuthorizationUrl();
        $uri = parse_url($url);
        parse_str($uri['query'], $query);

        $this->assertArrayHasKey('client_id', $query);
        $this->assertArrayHasKey('redirect_uri', $query);
        $this->assertArrayHasKey('state', $query);
        $this->assertArrayHasKey('scope', $query);
        $this->assertArrayHasKey('response_type', $query);
        $this->assertArrayHasKey('approval_prompt', $query);
        $this->assertNotNull($this->provider->getState());
    }

    public function testUrlAccessToken()
    {
        $url = $this->provider->getBaseAccessTokenUrl([]);
        $uri = parse_url($url);

        $this->assertEquals('/oauth2/access', $uri['path']);
    }

    public function testGetAccessToken()
    {
        $response = m::mock('GuzzleHttp\Psr7\Response');
        $response->shouldReceive('getHeader')->times(1);
        $response->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires_in": 3600, "refresh_token": "mock_refresh_token", "token_type": "bearer"}');

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post')->times(1);
        $client->shouldReceive('send')->times(1)->andReturn($response);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);

        $this->assertEquals('mock_access_token', $token->getToken());
        $this->assertLessThanOrEqual(time() + 3600, $token->getExpires());
        $this->assertGreaterThanOrEqual(time(), $token->getExpires());
        $this->assertEquals('mock_refresh_token', $token->getRefreshToken());
    }

    public function testScopes()
    {
        $this->assertEquals(['basic'], $this->provider->getDefaultScopes());
    }

    public function testUserData()
    {
        $userInfo     = '{
            "id": 123456,
            "name": "fake name",
            "status": "active",
            "joined": 1024067371000,
            "city": "Brooklyn",
            "country": "us",
            "localized_country_name": "États-Unis",
            "state": "NY",
            "lat": 40.67,
            "lon": -73.99,
            "photo": {
                "id": 26986762,
                "highres_link": "https://secure.meetupstatic.com/photos/member/b/6/a/a/highres_26986762.jpeg",
                "photo_link": "mock-photo-url",
                "thumb_link": "https://secure.meetupstatic.com/photos/member/b/6/a/a/thumb_26986762.jpeg",
                "type": "member",
                "base_url": "https://secure.meetupstatic.com"
            }
        }';
        $postResponse = m::mock('GuzzleHttp\Psr7\Response');
        $postResponse->shouldReceive('getBody')->times(1)->andReturn('{"access_token": "mock_access_token", "expires": 3600, "refresh_token": "mock_refresh_token", "token_type": "bearer"}');
        $postResponse->shouldReceive('getHeader')->times(1);

        $getResponse = m::mock('GuzzleHttp\Psr7\Response');
        $getResponse->shouldReceive('getBody')->times(1)->andReturn($userInfo);
        $getResponse->shouldReceive('getHeader')->times(1);

        $client = m::mock('GuzzleHttp\Client');
        $client->shouldReceive('setBaseUrl')->times(1);
        $client->shouldReceive('post')->times(1);
        $client->shouldReceive('send')->times(1)->andReturn($postResponse);
        $client->shouldReceive('get')->times(1);
        $client->shouldReceive('send')->times(1)->andReturn($getResponse);
        $this->provider->setHttpClient($client);

        $token = $this->provider->getAccessToken('authorization_code', ['code' => 'mock_authorization_code']);
        $user  = $this->provider->getResourceOwner($token);

        $this->assertEquals(123456, $user->getId());
        $this->assertEquals("fake name", $user->getName());
        $this->assertEquals("mock-photo-url", $user->getAvatar());
        $this->assertEquals(null, $user->getEmail());
    }
}