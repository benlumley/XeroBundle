<?php

namespace FL\XeroBundle\XeroPHP;

use Doctrine\ORM\EntityManager;
use XeroPHP\Application;
use XeroPHP\Application\PartnerApplication;
use XeroPHP\Application\PrivateApplication;
use XeroPHP\Application\PublicApplication;

class ApplicationFactory
{

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $config;

    /**
     * @return Application
     */
    public function createApplication(array $config, EntityManager $em)
    {
        $this->config = $config;

        $provider = new \Calcinai\OAuth2\Client\Provider\Xero([
                                                                  'clientId'          => $config['oauth']['client_id'],
                                                                  'clientSecret'      => $config['oauth']['client_secret'],
                                                                  'redirectUri'       => $config['oauth']['redirect_uri'],
                                                              ]);
        $repo = $em->getRepository('App:Oauth2RefreshToken');
        $refreshToken = $repo->findOneBySlug('xero');
        $newAccessToken = $provider->getAccessToken('refresh_token', [
            'refresh_token' => $refreshToken->getToken()
        ]);

        $refreshToken->setToken($newAccessToken->getRefreshToken());
        $em->flush($refreshToken);
        $tenants = $provider->getTenants($newAccessToken);

        return new Application($newAccessToken->getToken(), $tenants[0]->tenantId);
    }
}
