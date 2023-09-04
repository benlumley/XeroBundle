<?php

namespace FL\XeroBundle\XeroPHP;

use Calcinai\OAuth2\Client\Provider\Exception\XeroProviderException;
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

        $sql = "SELECT * FROM oauth2refresh_token WHERE slug = 'xero'";
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $token_row = $stmt->fetch();
        $refreshToken = $token_row['token'];
        try {
            $newAccessToken = $provider->getAccessToken('refresh_token', [
                'refresh_token' => $refreshToken
            ]);

            $sql = "UPDATE oauth2refresh_token SET token = :token, updated_at = NOW() WHERE slug = 'xero'";
            $stmt = $em->getConnection()->prepare($sql);
            $stmt->bindValue('token', $newAccessToken->getRefreshToken());
            $stmt->execute();
            $tenants = $provider->getTenants($newAccessToken);
            return new Application($newAccessToken->getToken(), $tenants[0]->tenantId);
        } catch (XeroProviderException $e) {
            return new Application('', '');
        }



    }
}
