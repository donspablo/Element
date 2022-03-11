<h1 align="center">Element (aka Jnr.) Project</h1>

<p align="center"><img align="center" src="assets/image (2).gif" /></p>

<p >⚡Element (aka jnr.) is 500X Faster Caching than Redis/Memcache/APC in PHP & HHVM. Utilizing a flat-file database system stored as json objects in files on the disc drive. The PHP API Service, which was developed as part of the open-source initiative, is used by Element to connect to the database.</p > 
<p >Element CSS's user interface (UI) and backend software are both free and open-source, which means they can be downloaded and used without restriction. The user interface (UI) and backend software for Element CSS are both available for download and installation on a personal computer.</p>

# Performance

<p >Opcache, a mechanism for in-memory file caching, is used to cache both application data and PHP core code. While HHVM has always supported this approach, PHP did not start supporting it until PHP version 7. Despite the fact that the method still works in subsequent releases of PHP it just isn't that fast.</p >
<p >Unlike other PHP caching methods, such as Redis and Memcache, which require serializing and deserializing objects, the flat-file disc storage strategy does not necessitate the use of PHP's serialize and json encode functions, as is commonly done. By keeping PHP objects in file cache memory between queries, it is possible to completely skip the need for serialization.</p >

# Example

<p>To demonstrate these concepts, we created a class called "FileCache" and assigned it the function "file_store":</p>

```
class FileCache {

  /**********************************
  
  # var_export(mixed $value, bool $return = false): ?string
  
  variable	Required. Specifies the variable to check
  return	Optional. If set to true, it returns the variable representation instead of outputting it
  
  # file_put_contents( string $filename, mixed $data, int $flags = 0, ?resource $context = null ): int|false
  
  FILE_USE_INCLUDE_PATH	Search for filename in the include directory. See include_path for more information.
  FILE_APPEND	If file filename already exists, append the data to the file instead of overwriting it.
  LOCK_EX	Acquire an exclusive lock on the file while proceeding to the writing. In other words, a flock() call happens between the fopen() call and the fwrite() call. This is not identical to an fopen() call with mode "x".
  
  **********************************/
  
  public static function file_store($key, $val) {
    file_put_contents($key, '<?=' . str_replace('stdClass::__set_state', '(object)', var_export($val, true)) . ';', LOCK_EX);
    
  }
  public static function cache_get($key) {
    @include "$key";
    return isset($val) ? $val : false;
  }
  
}
```

<p>The APC Cache was used to benchmark the technique, and the cache items were assessed using profiling code to establish their effectiveness:<p>

```

    /**********************************
    
    # microtime(bool $as_float = false): string|float
    
    microtime() returns the current Unix timestamp with microseconds. This function is only available on operating systems that support the gettimeofday() system call.
    
    # apc_store ( array $values [, mixed $unused = NULL [, int $ttl = 0 ]] ) : array
    
    Cache a variable in the data store.
    Note: Unlike many other mechanisms in PHP, variables stored using apc_store() will persist between requests (until the value is removed from the cache).
    
    **********************************/

    $CACHE_DATA = array_fill(0, 1000000, ‘yo’);
    $CACHE_KEY = array_fill(0, 1000000, ‘yo’);
    
    /***********************************************/
    
    // ---> INIT: file_store
    FileCache->file_store($CACHE_KEY, $CACHE_DATA);
    
    /***********************************************/
    
    // ---> INIT: apc_store
    apc_store($CACHE_KEY, $CACHE_DATA);
    
    /***********************************************/    
    
    // ---> START: file_fetch
    
    $A = microtime(true);
    FileCache->file_fetch($CACHE_KEY);
    microtime(true) - $A; 
    
    // ---> RESULTS: 0.00013017654418945
    
    /***********************************************/
    
    // ---> START: apc_fetch
    
    $B = microtime(true);
    apc_fetch($CACHE_KEY);
    microtime(true) - $B; 
    
    // ---> RESULTS: 0.061056137084961

```

<p>We were able to fetch a million-row array of strings in a tenth of a millisecond,450 times faster than APC. The 450X number is kind of arbitrary, because larger objects will have larger performance improvements, approaching infinity. To be clear, though, we did see speed improvements of two orders of magnitude in our real production object caching when we implemented this method.</p>
<p>Keep in mind that PHP file caching should primarily be used for arrays & objects, not strings, since there is no performance benefit for strings. In fact, APC is a tad bit faster when dealing with short strings, due to the slight overhead of calling PHP’s include() function.</p>

## Index

- [Road Map](#Road-Map)
- [Installation](#Installation-Instructions)
- [Usage & How to Guide](#Usage-&-How-to-Guide)
- [Contributing to the Project](#Contributing-to-the-Project)
- [Licensing and Ownership](#Licensing-and-Ownership)



<a href="https://render.com/deploy?repo=https://github.com/donPabloNow/Element"><img align="center" src="https://render.com/images/deploy-to-render-button.svg" /></a>
<a href="https://vercel.com/new/clone?repository-url=https://github.com/donPabloNow/Element"><img align="center" src="https://vercel.com/button" /></a>
<a href="https://www.heroku.com/deploy/?template=https://gitpod.io/#https://github.com/donPabloNow/Element"><img align="center" src="https://www.herokucdn.com/deploy/button.svg" /></a>
<a href="https://gitpod.io/#https://github.com/donPabloNow/Element"><img align="center" src="https://gitpod.io/button/open-in-gitpod.svg" /></a>


<br />

<p align="center"><br /><img align="center" src="assets/image (3).gif" /><br /></p>

<p align="center"><br /><img align="center" src="assets/image (1).gif" /><br /></p>

<br />


## Road Map

- [x] Foundation
    - [x] API Service
    - [x] TxT DB
    - [x] Login
    - [x] Registration
    - [x] Dashboard
    - [x] Who is Online
    - [x] Documentation
- [ ] Tables
    - [x] Users
    - [x] Notes
    - [x] Tasks
    - [ ] Chat
- [ ] Notes
    - [x] Data Scheme
    - [x] API Endpoints
    - [x] Functions
    - [ ] UI
- [ ] Tasks
    - [x] Data Scheme
    - [x] API Endpoints
    - [x] Functions
    - [ ] UI
- [ ] Chat
    - [ ] Data Scheme
    - [x] API Endpoints
    - [x] Functions
    - [ ] UI
- [ ] Testing
    - [x] BUILDs
    - [x] Owners
    - [ ] Crate details


<br />

<p align="center"><br /><img align="center" src="assets/image (3).gif" /><br /></p>

<p align="center"><br /><img align="center" src="assets/image (4).gif" /><br /></p>

<br />


## Installation Instructions

# Lando

You may either "plug and play" on PHP-compatible machines or build in a LAMP environment using the project's LAMP recipe
for Lando - A Liberating Dev Tool For All Your Projects, which can be found here. If you want to learn more about the
project, check out the FAQ. The use of local development and DevOps technologies by professional developers is
widespread around the globe, while it is most prominent in the United States. Release oneself from the mental
restrictions imposed by inadequate software for development. You may be able to save time, money, and frustration if you
concentrate your efforts on the most important tasks.

![image](https://user-images.githubusercontent.com/6468571/152177774-25482b2a-f8cd-4f19-a221-97dc29212a2d.png)

Clone this repo

```

git clone https://github.com/donPabloNow/element

```

Clone the "sample.env" to ".env" and update with the correct details.

```
cp ./sample.env ./.env
```

Host the files on a PHP server

```
# Start it up
lando start

# List information about this app.
lando info
```

or

```
# Initialize a lamp recipe using the latest codeigniter BUILD
lando init \
  --source remote \
  --remote-url https://github.com/bcit-ci/CodeIgniter/archive/3.1.10.tar.gz \
  --remote-options="--strip-components 1" \
  --recipe lamp \
  --webroot . \
  --name my-first-lamp-app
```

For more information please see: https://docs.lando.dev/config/lamp.html

![image](https://user-images.githubusercontent.com/6468571/152178164-3cf9d286-6ca2-407e-8f62-50fc4d217a6b.png)

![image](https://user-images.githubusercontent.com/6468571/152181962-33e4e658-5fbc-4b2d-9366-7147e9fabe65.png)

# Gitpod

Gitpod is an open-source Kubernetes tool for quickly establishing code-ready development environments. It produces
fresh, automated development environments in the cloud for each work utilising cloud-based technologies. And it does all
of this in the cloud. It enables you to declare your development environment in code, as well as to launch immediate,
remote, and cloud-based development environments directly from your browser or desktop integrated development
environment.

https://gitpod.com/#https://github.com/donPabloNow/element

<p align="center"><br /><img align="center" src="https://user-images.githubusercontent.com/6468571/152177615-421c1286-33cd-4c38-9f7b-3c486901ba81.png" /><br /></p>

<br />

<p align="center"><br /><img align="center" src="assets/image (3).gif" /><br /></p>

<p align="center"><br /><img align="center" src="assets/image (5).gif" /><br /></p>

<br />


## Usage & How to Guide

Navigate to the root of the project with your browser, register an account and then login.

![image](https://user-images.githubusercontent.com/6468571/152181949-99b9aaa6-586e-4f64-826d-ec7616535d1c.png)

<br />

<p align="center"><br /><img align="center" src="assets/image (3).gif" /><br /></p>

<p align="center"><br /><img align="center" src="assets/image (9).gif" /><br /></p>

<br />


## Contributing to the Project

Pull requests are evaluated and approved by the development team. If you want to talk about the changes you want to
make, please create a new issue for that purpose. If possible, please ensure that tests are updated on a regular basis
in order to avoid misconceptions.

![image](https://user-images.githubusercontent.com/6468571/152181932-88f8e56c-b479-478a-8e38-06150cf4ef3e.png)

![image](https://user-images.githubusercontent.com/6468571/152178640-266dfe32-62c2-4ad2-a2c9-2096af248e18.png)

![image](https://user-images.githubusercontent.com/6468571/152181962-33e4e658-5fbc-4b2d-9366-7147e9fabe65.png)


<br />

<p align="center"><br /><img align="center" src="assets/image (3).gif" /><br /></p>

<p align="center"><br /><img align="center" src="assets/image (6).gif" /><br /></p>

<br />


<h2 align="center">Licensing and Ownership</h2>

<p align="center"><a href="https://choosealicense.com/licenses/mit/">MIT License Agreeemnt 2022</a>
<p align="center"><a href="https://github.com/donpablonow/">@donpablonow</a>