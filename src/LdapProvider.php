<?php

namespace RiCi12\LdapLaravelProvider;

use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use \Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class LdapProvider implements UserProvider {

    /**
     * The hasher implementation.
     *
     * @var \Illuminate\Contracts\Hashing\Hasher
     */
    protected $hasher;

    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Server URL
     * @var string
     */
    protected $ldapServer = '';

    /**
     * Domain name, can be empty
     * @var string
     */
    protected $ldapDomainName = '';

    /**
     * Name of username attribute from credentials input
     * @var string
     */
    protected $usernameCredentialsAttribute = 'username';

    /**
     * Name of password atribute from credentials input
     * @var string
     */
    protected $passwordCredentialsAttribute = 'password';

    /**
     * Connect to server, return true if credentials are accepted, false otherwise
     * @param array $credentials
     * @return bool
     * @throws BindingErrorException
     */
    private function connectToServer(array $credentials) {
        try {
            $ldapconn = ldap_connect($this->ldapServer);
            if($ldapconn) {
                return @ldap_bind($ldapconn,
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
     * @param HasherContract $hasher
     * @param $model
     */
    public function __construct(HasherContract $hasher, $model) {
        $this->model = $model;
        $this->hasher = $hasher;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        //return User::find($identifier);
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by by their unique identifier and "remember me" token.
     *
     * @param  mixed $identifier
     * @param  string $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {}

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
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if($this->connectToServer($credentials)) {
            //return User::where('username', $credentials[$this->usernameCredentialsAttribute])->first();
            return $this->createModel()->newQuery()->where('username', $credentials[$this->usernameCredentialsAttribute])->first();
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

