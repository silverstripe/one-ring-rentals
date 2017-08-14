<?php

use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse_Exception;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\GraphQL\Auth\AuthenticatorInterface;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\BasicAuth;
use SilverStripe\Security\DefaultAdminService;

/**
 * An authenticator using SilverStripe's BasicAuth
 *
 * @package silverstripe-graphql
 */
class OneRingAuthenticator implements AuthenticatorInterface
{
    /**
     * Given the current request, require basic auth matching a member object. (See BasicAuth for details).
     * In our development environment, don't require authentication.
     *
     * @param HTTPRequest $request
     * @return null|\SilverStripe\Security\Member
     * @throws ValidationException
     */
    public function authenticate(HTTPRequest $request)
    {
        if(Director::isDev()){
            return Injector::inst()->get('SilverStripe\Security\DefaultAdminService')->findOrCreateDefaultAdmin();
        }

        try {
            return BasicAuth::requireLogin($request, 'Restricted resource');
        } catch (HTTPResponse_Exception $ex) {
            // BasicAuth::requireLogin may throw its own exception with an HTTPResponse in it
            $failureMessage = (string)$ex->getResponse()->getBody();
            throw new ValidationException($failureMessage, 401);
        }
    }

    /**
     * Determine if this authenticator is applicable to the current request
     *
     * @param HTTPRequest $request
     * @return bool
     */
    public function isApplicable(HTTPRequest $request)
    {
        if(Director::isDev()){
            return true;
        }

        if ($this->hasAuthHandler('HTTP_AUTHORIZATION')
            || $this->hasAuthHandler('REDIRECT_HTTP_AUTHORIZATION')
        ) {
            return true;
        }
        if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
            return true;
        }
        return false;
    }

    /**
     * Check for $_SERVERVAR with basic auth credentials
     *
     * @param  string $servervar
     * @return bool
     */
    protected function hasAuthHandler($servervar)
    {
        return isset($_SERVER[$servervar]) && preg_match('/Basic\s+(.*)$/i', $_SERVER[$servervar]);
    }
}
