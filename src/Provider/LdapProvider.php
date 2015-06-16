<?php

namespace RiCi12\LdapLaravelProvider\Provider;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use \Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\App;
use RiCi12\LdapLaravelProvider\Exception\BindingErrorException;
use RiCi12\LdapLaravelProvider\Exception\UserModelNotFoundException;

class LdapProvider implements UserProvider 
{

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Must be set in.env
     * See documentation.
     * @var string
     */
    protected $ldapServer, $ldapDomainName,$usernameCredentialsAttribute,$passwordCredentialsAttribute;

    /**
     * Connect to server, return true if credentials are accepted, false otherwise
     * @param array $credentials
     * @return bool
     * @throws BindingErrorException
     */
    private function connectToServer(array $credentials) 
    {
        try {
            $ldapconn = ldap_connect($this->ldapServer);
            if($ldapconn) {
                return @ldap_bind(
                    $ldapconn,
                    $this->ldapDomainName.$credentials[$this->usernameCredentialsAttribute],
                    $credentials[$this->passwordCredentialsAttribute]
                );
            }
            return false;
        } catch (Exception $e) {
            throw new BindingErrorException();
        }
    }

    /**
     * Create a new ldap user provider
     * @param $model
     */
    public function __construct($model) 
    {
        $this->model = $model;
        $this->ldapServer = env('LDAPSERVER');
        $this->ldapDomainName = env('LDAPDOMAINNAME');
        $this->usernameCredentialsAttribute = env('USERNAMECREDENTIALSATTRIBUTE');
        $this->passwordCredentialsAttribute = env('PASSWORDCREDENTIALSATTRIBUTE');
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return Authenticatable|null
     * @throws UserModelNotFoundException
     */
    public function retrieveById($identifier)
    {
        try {
            return $this->createModel()->newQuery()->findOrFail($identifier);
        } catch (Exception $e) {
            throw new UserModelNotFoundException();
        }
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();
        return $model->newQuery()
            ->where($model->getKeyName(), $identifier)
            ->where($model->getRememberTokenName(), $token)
            ->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  string $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array $credentials
     * @return Authenticatable|null
     * @throws BindingErrorException
     * @throws UserModelNotFoundException
     */
    public function retrieveByCredentials(array $credentials)
    {
        //Check credentials
        if($this->connectToServer($credentials)) {
            //Search user model in the "second" db
            $user = $this->createModel()->newQuery()->where('username', $credentials[$this->usernameCredentialsAttribute])->first();
            if($user != null)
                //If a user is found, return it
                return $user;
            //No model found
            throw new UserModelNotFoundException();
        }
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable $user
     * @param  array $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        return $this->connectToServer($credentials);
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

}

