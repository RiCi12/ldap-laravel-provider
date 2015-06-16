# LdapLaravelProvider
Lean and simple ldap provider for Laravel 5.1.

### When is this package useful?
When you need to authenticate your user against a ldap server, while maintaining a *users* table in your database.

### What is included?
A provider and its service provider.

### How to install
In your composer.json, as a *required* dependency, insert:
```json
"rici12/ldap-laravel-provider": "0.*"
```
Then run 
```
composer update
```
After that, open *config/app.php* and add to your *service providers* list
```
RiCi12\LdapLaravelProvider\ServiceProvider\LdapServiceProvider::class
```
Then open *config/auth.php* and change your *driver* option
```
'driver' => 'ldap'
```
Open the *.env* file and add the required options
```
LDAPSERVER = auth.magrathea.com 
LDAPDOMAINNAME = MAGRATHEA\ 
USERNAMECREDENTIALSATTRIBUTE = username 
PASSWORDCREDENTIALSATTRIBUTE = password
```

### Work flow
1. Try to connect to the provided server
2. Check if input credentials are correct
3. If true, search for a user from *users* table (compairing 'username' attribute)
4. Return the user, if found; otherwise, throws an Exception.

### Use case
In my last project I needed to let the user authenticate with their domain credentials while not letting everybody get access to the homepage; plus, I was asked to implement a really simple roles system (read/write access).
So i implemented this authentication service provider wich fits great with Laravel Auth system: you'll be able to access the user from *Auth facade*.