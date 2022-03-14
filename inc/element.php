<?php

namespace Element\inc;

use stdClass;
use ZipArchive;

if (defined('PHPUNIT_TESTING') === false) {
    $Element = new Element();
    $Element->init();
    $Element->render();
}

class Element
{
    public $currentSite = '';
    public $currentSiteTree = [];
    public $currentSiteExists = false;
    public $loggedIn = false;
    public $listeners = [];
    public $dataPath;
    public $filesPath;
    public $headerResponseDefault = true;
    public $headerResponse = 'HTTP/1.0 200 OK';
    private $db;
    private $modulesCachePath;
    private $securityCachePath;
    private $dbPath;

    public function __construct(string $dataFolder = 'data', string $filesFolder = 'files', string $dbName = 'data.json')
    {
        $this->setPaths($dataFolder, $filesFolder, $dbName);
        $this->db = $this->getDb();
    }

    public function setPaths(string $dataFolder = 'data', string $filesFolder = 'files', string $dbName = 'data.json'): void
    {
        $this->dataPath = sprintf('%s/%s', $GLOBALS['APP_DIR'], $dataFolder);
        $this->dbPath = sprintf('%s/%s', $this->dataPath, $dbName);
        $this->filesPath = sprintf('%s/%s', $this->dataPath, $filesFolder);
        $this->modulesCachePath = sprintf('%s/%s', $this->dataPath, 'cache.json');
        $this->securityCachePath = sprintf('%s/%s', $this->dataPath, 'security.json');
    }

    public function getDb(): stdClass
    {
        if (!file_exists($this->dbPath)) {
            $this->checkFolder(dirname($this->dbPath));
            $this->checkFolder($this->filesPath);
            $this->checkFolder($GLOBALS['APP_DIR'] . '/' . $GLOBALS['FILES_DIR']);
            $this->checkFolder($GLOBALS['APP_DIR'] . '/' . $GLOBALS['PLUGINS_DIR']);
            $this->createDb();
        }
        return json_decode(file_get_contents($this->dbPath), false);
    }

    public function checkFolder(string $folder): void
    {
        if (!is_dir($folder) && !mkdir($folder, 0755) && !is_dir($folder)) {
            throw new Exception('Could not create data folder.');
        }
        if (!is_writable($folder)) {
            throw new Exception('Could write to data folder.');
        }
    }

    public function createDb(): void
    {
        $this->checkMinimumRequirements();
        $password = $this->generatePassword();
        $this->db = (object)[$GLOBALS['DB_CONFIG'] =>

            ['title' => 'Website title',
                'files' => 'element',
                'defaultSite' => 'home',
                'login' => 'cms',
                'forceLogout' => false,
                'forceHttps' => false,
                'saveChangesPopup' => false,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'lastLogins' => [],
                'lastModulesSync' => null,
                'customModules' => ['files' => [], 'plugins' => []],
                'navigationItems' => ['0' => ['name' => 'Home', 'slug' => 'home', 'visibility' => 'show', $GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE'] => (object)array()],
                    '1' => ['name' => 'How to', 'slug' => 'how-to', 'visibility' => 'show',
                        $GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE'] => (object)array()]]],
            'sites' => ['404' => ['title' => '404', 'keywords' => '404', 'description' => '404', 'content' => '<div style="text-align: center;"><h1>404 - Site not found</h1></div>',
                $GLOBALS['DB_SITES_SUBSITE_KEY'] => (object)array()],
                'home' => ['title' => 'Home', 'keywords' => 'Enter, site, keywords, for, search, engines',
                    'description' => 'A site description is also good for search engines.', 'content' => '<h1>Welcome to your website</h1>
<p>Your password for editing everything is: <b>' . $password . '</b></p>

<p><a href="' . self::url('cms') . '" class="button">Click here to login</a></p>

<p>To install an awesome editor, open options/Plugins and click Install Summernote.</p>', $GLOBALS['DB_SITES_SUBSITE_KEY'] => (object)array()], 'how-to' => ['title' => 'How to', 'keywords' => 'Enter, keywords, for, this site', 'description' => 'A site description is also good for search engines.', 'content' => '<h2>Easy editing</h2>
<p>After logging in, click anywhere to edit and click outside to save. Changes are live and shown immediately.</p>

<h2>Create new site</h2>
<p>Sites can be created in the options.</p>

<h2>Start a blog or change your files</h2>
<p>To install, update or remove files/plugins, visit the options.</p>

<h2><b>Support element</b></h2>
<p>element is free for over 12 years.<br>
<a href="https://swag.element.com" target="_blank"><u>Click here to support us by getting a T-shirt</u></a> or <a href="https://www.element.com/donate" target="_blank"><u>with a donation</u></a>.</p>', $GLOBALS['DB_SITES_SUBSITE_KEY'] => (object)array()]], 'widgets' => ['subside' => ['content' => '<h2>About your website</h2>

<br>
<p>Website description, contact form, mini map or anything else.</p>
<p>This editable area is visible on all sites.</p>'], 'footer' => ['content' => '&copy;' . date('Y') . ' Your website']]];
        $this->save();
    }

    private function checkMinimumRequirements(): void
    {
        if (floatval(phpversion()) <= 7.2) {
            die('<p>We have detected that your server is running PHP Version ' . phpversion() . ', unfortunately Element requires PHP version 7.2 or higher. Please will you upgrade the PHP version on this server or use another server to host this platform.</p>');
        }
        $extensions = ['curl', 'zip', 'mbstring'];
        $missingExtensions = [];
        foreach ($extensions as $ext) {
            if (!extension_loaded($ext)) {
                $missingExtensions[] = $ext;
            }
        }
        if (!empty($missingExtensions)) {
            die('<p>The following extensions are required: ' . implode(', ', $missingExtensions) . '. Contact your host or configure your server to enable them with correct permissions.</p>');
        }
    }

    public function generatePassword(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        return substr(str_shuffle($characters), 0, $GLOBALS['MIN_PASSWORD_LENGTH']);
    }

    public static function url(string $location = ''): string
    {
        $showHttps = Element::isCurrentlyOnSSL();
        $dataPath = sprintf('%s/%s', __DIR__, 'data');
        $securityCachePath = sprintf('%s/%s', $dataPath, 'security.json');
        if (is_file($securityCachePath) && file_exists($securityCachePath)) {
            $securityCache = json_decode(file_get_contents($securityCachePath), true);
            $showHttps = $securityCache['forceHttps'] ?? false;
        }
        return ($showHttps ? 'https' : 'http') . '://' . $_SERVER['SERVER_NAME'] . ((($_SERVER['SERVER_PORT'] === '80') || ($_SERVER['SERVER_PORT'] === '443')) ? '' : ':' . $_SERVER['SERVER_PORT']) . ((dirname($_SERVER['SCRIPT_NAME']) === '/') ? '' : dirname($_SERVER['SCRIPT_NAME'])) . '/' . $location;
    }

    public static function isCurrentlyOnSSL(): bool
    {
        return (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) === 'on') || (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) === 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');
    }

    public function save(string $path = null, object $content = null): void
    {
        $path = $path ?? $this->dbPath;
        $content = $content ?? $this->db;
        $json = json_encode($content, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if (empty($content) || empty($json) || json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = sprintf('%s - Error while trying to save in %s: %s', time(), $path, print_r($content, true));
            try {
                $randomNumber = random_bytes(8);
            } catch (Exception $e) {
                $randomNumber = microtime(false);
            }
            $logName = date('Y-m-d H:i:s') . '-error-' . bin2hex($randomNumber) . '.log';
            $logsPath = sprintf('%s/data/logs', $GLOBALS['APP_DIR']);
            $this->checkFolder($logsPath);
            error_log($errorMessage, 3, sprintf('%s/%s', $logsPath, $logName));
            return;
        }
        file_put_contents($path, $json, LOCK_EX);
    }

    public function init(): void
    {
        $this->forceSSL();
        $this->loginStatus();
        $this->siteStatus();
        $this->logoutAction();
        $this->loginAction();
        $this->notFoundResponse();
        $this->loadPlugins();
        if ($this->loggedIn) {
            $this->manuallyRefreshCacheData();
            $this->addCustomModule();
            $this->installUpdateModuleAction();
            $this->changePasswordAction();
            $this->deleteFileModuleAction();
            $this->changeSiteFilesAction();
            $this->backupAction();
            $this->forceHttpsAction();
            $this->saveChangesPopupAction();
            $this->deleteSiteAction();
            $this->saveAction();
            $this->updateAction();
            $this->uploadFileAction();
            $this->notifyAction();
        }
    }

    private function forceSSL(): void
    {
        if ($this->isHttpsForced() && !Element::isCurrentlyOnSSL()) {
            $this->updateSecurityCache();
            $this->redirect();
        }
    }

    private function isHttpsForced(): bool
    {
        return $this->get('config', 'forceHttps') ?? false;
    }

    public function get()
    {
        $args = func_get_args();
        $object = $this->db;
        foreach ($args as $key => $arg) {
            $object = $object->{$arg} ?? $this->set(...array_merge($args, [null]));
        }
        return $object;
    }

    public function set(): void
    {
        $args = func_get_args();
        $value = array_pop($args);
        $lastKey = array_pop($args);
        $data = $this->db;
        foreach ($args as $arg) {
            $data = $data->{$arg};
        }
        $data->{$lastKey} = $value;
        $this->save();
    }

    public function updateSecurityCache(): void
    {
        $content = ['forceHttps' => $this->isHttpsForced()];
        $json = json_encode($content, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        file_put_contents($this->securityCachePath, $json, LOCK_EX);
    }

    public function redirect(string $location = ''): void
    {
        header('Location: ' . self::url($location));
        die();
    }

    public function loginStatus(): void
    {
        $this->loggedIn = $this->get('config', 'forceLogout') ? false : isset($_SESSION['loggedIn'], $_SESSION['rootDir']) && $_SESSION['rootDir'] === $GLOBALS['APP_DIR'];
    }

    public function siteStatus(): void
    {
        $this->currentSite = $this->parseUrl() ?: $this->get('config', 'defaultSite');
        $this->currentSiteExists = !empty($this->getCurrentSiteData());
    }

    public function parseUrl(): string
    {
        $site = $_GET['site'] ?? null;
        if (!isset($site) || !$site) {
            $defaultSite = $this->get('config', 'defaultSite');
            $this->currentSiteTree = explode('/', $defaultSite);
            return $defaultSite;
        }
        $this->currentSiteTree = explode('/', rtrim($site, '/'));
        if ($site === $this->get('config', 'login')) {
            return htmlspecialchars($site, ENT_QUOTES);
        }
        $currentSite = end($this->currentSiteTree);
        return $this->slugify($currentSite);
    }

    public function slugify(string $text): string
    {
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim(htmlspecialchars(mb_strtolower($text), ENT_QUOTES), '/');
        $text = trim($text, '-');
        return empty($text) ? '-' : $text;
    }

    public function getCurrentSiteData(): ?object
    {
        return $this->getSiteData(implode('/', $this->currentSiteTree));
    }

    public function getSiteData(string $slugTree): ?object
    {
        $arraySlugTree = explode('/', $slugTree);
        $siteData = null;
        foreach ($arraySlugTree as $slug) {
            if ($siteData === null) {
                $siteData = $this->get($GLOBALS['DB_SITES_KEY'])->{$slug} ?? null;
                continue;
            }
            $siteData = $siteData->{$GLOBALS['DB_SITES_SUBSITE_KEY']}->{$slug} ?? null;
            if (!$siteData) {
                return null;
            }
        }
        return $siteData;
    }

    public function logoutAction(bool $forceLogout = false): void
    {
        if ($forceLogout || ($this->currentSite === 'logout' && isset($_REQUEST['token']) && $this->hashVerify($_REQUEST['token']))) {
            unset($_SESSION['loggedIn'], $_SESSION['rootDir'], $_SESSION['token'], $_SESSION['alert']);
            $this->redirect($this->get('config', 'login'));
        }
    }

    public function hashVerify(string $token): bool
    {
        return hash_equals($token, $this->getToken());
    }

    public function getToken(): string
    {
        return $_SESSION['token'] ?? $_SESSION['token'] = bin2hex(random_bytes(32));
    }

    public function loginAction(): void
    {
        if ($this->currentSite !== $this->get('config', 'login')) {
            return;
        }
        if ($this->loggedIn) {
            $this->redirect();
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        $password = $_POST['password'] ?? '';
        if (password_verify($password, $this->get('config', 'password'))) {
            session_regenerate_id(true);
            $_SESSION['loggedIn'] = true;
            $_SESSION['rootDir'] = $GLOBALS['APP_DIR'];
            $this->set('config', 'forceLogout', false);
            $this->saveAdminLoginIP();
            $this->redirect();
        }
        $this->alert('test', '<script>alert("Wrong password")</script>', 1);
        $this->redirect($this->get('config', 'login'));
    }

    private function saveAdminLoginIP(): void
    {
        $getAdminIP = $_SERVER['HTTP_CLIENT_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        if ($getAdminIP === null) {
            return;
        }
        if (!$savedIPs = $this->get('config', 'lastLogins')) {
            $this->set('config', 'lastLogins', []);
            $savedIPs = [];
        }
        $savedIPs = (array)$savedIPs;
        $savedIPs[date('Y/m/d H:i:s')] = $getAdminIP;
        krsort($savedIPs);
        $this->set('config', 'lastLogins', array_slice($savedIPs, 0, 5));
    }

    public function alert(string $class, string $message, bool $sticky = false): void
    {
        if (isset($_SESSION['alert'][$class])) {
            foreach ($_SESSION['alert'][$class] as $v) {
                if ($v['message'] === $message) {
                    return;
                }
            }
        }
        $_SESSION['alert'][$class][] = ['class' => $class, 'message' => $message, 'sticky' => $sticky];
    }

    public function notFoundResponse(): void
    {
        if (!$this->loggedIn && !$this->currentSiteExists && $this->headerResponseDefault) {
            $this->headerResponse = 'HTTP/1.1 404 Not Found';
        }
    }

    public function loadPlugins(): void
    {
        $plugins = $GLOBALS['APP_DIR'] . '/plugins';
        if (!is_dir($plugins) && !mkdir($plugins) && !is_dir($plugins)) {
            return;
        }
        if (!is_dir($this->filesPath) && !mkdir($this->filesPath) && !is_dir($this->filesPath)) {
            return;
        }
        foreach (glob($plugins . '/*', GLOB_ONLYDIR) as $dir) {
            if (file_exists($dir . '/' . basename($dir) . '.php')) {
                $dir . '/' . basename($dir) . '.php';
            }
        }
    }

    public function manuallyRefreshCacheData(): void
    {
        if (!isset($_REQUEST['manuallyResetCacheData']) || !$this->verifyFormActions(true)) {
            return;
        }
        $this->updateAndCacheModules();
        $this->checkElementCoreUpdate();
        $this->set('config', 'lastModulesSync', date('Y/m/d'));
        $this->redirect();
    }

    public function verifyFormActions(bool $isRequest = false): bool
    {
        return ($isRequest ? isset($_REQUEST['token']) : isset($_POST['token'])) && $this->loggedIn && $this->hashVerify($isRequest ? $_REQUEST['token'] : $_POST['token']);
    }

    private function updateAndCacheModules(): void
    {
        $this->set('config', 'lastModulesSync', date('Y/m/d'));
        $this->cacheModulesData();
    }

    private function cacheModulesData(): void
    {
        $db = $this->getDb();
        $this->updateModulesCache();
        $returnArray = $this->getJsonFileData($this->modulesCachePath);
        $arrayCustom = (array)$db->config->customModules;
        foreach ($arrayCustom as $type => $modules) {
            foreach ($modules as $url) {
                $elementModuleData = $this->fetchModuleConfig($url, $type);
                if (null === $elementModuleData) {
                    continue;
                }
                $name = $elementModuleData->dirName;
                $elementModuleData = $this->moduleCacheParser($elementModuleData, $name);
                $returnArray[$type][$name] = $elementModuleData;
            }
        }
        $this->save($this->modulesCachePath, (object)$returnArray);
    }

    private function updateModulesCache(): void
    {
        $elementModules = trim($this->getFileFromRepo('element-modules.json'));
        $jsonObject = json_decode($elementModules);
        $parsedCache = $this->moduleCacheMapper($jsonObject);
        if (empty($parsedCache)) {
            return;
        }
        $this->save($this->modulesCachePath, $parsedCache);
    }

    public function getFileFromRepo(string $file): string
    {
        $repo = str_replace('https://github.com/', 'https://raw.githubusercontent.com/', $GLOBALS['ELEMENT_REPO']);
        return $this->downloadFileFromUrl($repo . $file);
    }

    private function downloadFileFromUrl(string $fileUrl): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $fileUrl);
        $content = curl_exec($ch);
        if (false === $content) {
            $this->alert('danger', 'Cannot get content from url.');
        }
        curl_close($ch);
        return (string)$content;
    }

    private function moduleCacheMapper(object $elementModule): object
    {
        $mappedModules = new stdClass;
        foreach ($elementModule as $type => $value) {
            if ($type === 'BUILD') {
                if ($value !== $GLOBALS['MODULES_JSON_BUILD']) {
                    $this->alert('danger', 'The element-modules.json BUILD is filesorrect');
                    break;
                }
                continue;
            }
            $mappedModules->{$type} = (object)array();
            foreach ($value as $moduleName => $module) {
                $parsedModule = $this->moduleCacheParser($module, $moduleName);
                if (empty($parsedModule)) {
                    continue;
                }
                /*$mappedModules->{$type}->{$moduleName} = (object)array();*/
                $mappedModules->{$type}->{$moduleName} = $parsedModule;
            }
        }
        return $mappedModules;
    }

    private function moduleCacheParser(object $module, string $moduleName): ?object
    {
        if (!$this->validateElementModuleStructure($module)) {
            return null;
        }
        return (object)["name" => $module->name, "dirName" => $moduleName, "repo" => $module->repo, "zip" => $module->zip, "summary" => $module->summary, "BUILD" => $module->BUILD, "image" => $module->image,];
    }

    private function validateElementModuleStructure(object $elementModule): bool
    {
        return property_exists($elementModule, 'name') && property_exists($elementModule, 'repo') && property_exists($elementModule, 'zip') && property_exists($elementModule, 'summary') && property_exists($elementModule, 'BUILD') && property_exists($elementModule, 'image');
    }

    public function getJsonFileData(string $path): ?array
    {
        if (is_file($path) && file_exists($path)) {
            return json_decode(file_get_contents($path), true);
        }
        return null;
    }

    private function fetchModuleConfig(string $url, string $type): ?object
    {
        $elementModules = json_decode(trim($this->downloadFileFromUrl($url)));
        $elementModulesData = $elementModules && property_exists($elementModules, $type) ? $elementModules->{$type} : null;
        if (null === $elementModulesData) {
            $this->alert('danger', 'The element-modules.json file does not contain all the required information.');
            return null;
        }
        $returnData = reset($elementModulesData);
        $name = key($elementModulesData);
        $returnData->dirName = $name;
        return $returnData;
    }

    private function checkElementCoreUpdate(): void
    {
        $onlineBUILD = $this->getOfficialBUILD();
        if ($onlineBUILD > BUILD) {
            $this->alert('info', '<h3>New element update available</h3>
				<a href="https://element.com/news" target="_blank"><u><b>Check what\'s new</b></u></a>
				and <b>backup your website</b> before updating.
				 <form action="' . $this->getCurrentSiteUrl() . '" method="post" class="marginTop5">
					<button type="submit" class="wbtn wbtn-info marginTop20" name="backup"><i class="installIcon"></i>Download backup</button>
					<div class="clear"></div>
					<button class="wbtn wbtn-info marginTop5" name="update"><i class="refreshIcon"></i>Update element ' . BUILD . ' to ' . $onlineBUILD . '</button>
					<input type="hidden" name="token" value="' . $this->getToken() . '">
				</form>');
        }
    }

    public function getOfficialBUILD(): ?string
    {
        return $this->getCheckFileFromRepo('BUILD');
    }

    public function getCheckFileFromRepo(string $fileName): ?string
    {
        $BUILD = trim($this->getFileFromRepo($fileName));
        return $BUILD === '404: Not Found' || $BUILD === '400: Invalid request' ? null : $BUILD;
    }

    public function getCurrentSiteUrl(): string
    {
        $path = '';
        foreach ($this->currentSiteTree as $parentSite) {
            $path .= $parentSite . '/';
        }
        return self::url($path);
    }

    public function addCustomModule(): void
    {
        if (!isset($_POST['pluginFilesUrl'], $_POST['pluginFilesType']) || !$this->verifyFormActions()) {
            return;
        }
        $type = $_POST['pluginFilesType'];
        $url = rtrim(trim($_POST['pluginFilesUrl']), '/');
        $customModules = (array)$this->get('config', 'customModules', $type);
        $elementModuleData = $this->fetchModuleConfig($url, $type);
        $errorMessage = null;
        switch (true) {
            case null === $elementModuleData || !$this->isValidModuleURL($url):
                $errorMessage = 'Invalid URL. The module URL needs to contain the full path to the raw element-modules.json file.';
                break;
            case !$this->validateElementModuleStructure($elementModuleData):
                $errorMessage = 'Module not added - the element-modules.json file does not contain all the required information.';
                break;
            case $this->checkIfModuleRepoExists($elementModuleData->repo, $type):
                $errorMessage = 'Module already exists.';
                break;
        }
        if ($errorMessage !== null) {
            $this->alert('danger', $errorMessage);
            $this->redirect();
        }
        $customModules[] = $url;
        $this->set('config', 'customModules', $type, $customModules);
        $this->cacheSingleCacheModuleData($url, $type);
        $this->alert('success', 'Module successfully added to <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#' . $type . '">' . ucfirst($type) . '</b></a>.');
        $this->redirect();
    }

    private function isValidModuleURL(string $url): bool
    {
        return strpos($url, 'element-modules.json') !== false;
    }

    private function checkIfModuleRepoExists(string $repo): bool
    {
        $data = $this->getModulesCachedData();
        return in_array($repo, array_column($data, 'repo'));
    }

    public function getModulesCachedData(): array
    {
        $this->checkModulesCache();
        $data = $this->getJsonFileData($this->modulesCachePath);
        return $data !== null && array_key_exists($GLOBALS['FILES_DIR'], $data) ? $data[$GLOBALS['FILES_DIR']] : [];
    }

    public function checkModulesCache(): void
    {
        $db = $this->getDb();
        $data = $this->getJsonFileData($this->modulesCachePath);
        $lastSync = $db->config->lastModulesSync ?? strtotime('-2 days');
        if (empty($data) || strtotime($lastSync) < strtotime('-1 days')) {
            $this->updateAndCacheModules();
        }
    }

    private function cacheSingleCacheModuleData(string $url, string $type): void
    {
        $returnArray = $this->getJsonFileData($this->modulesCachePath);
        $elementModuleData = $this->fetchModuleConfig($url, $type);
        if (null === $elementModuleData) {
            return;
        }
        $name = $elementModuleData->dirName;
        $elementModuleData = $this->moduleCacheParser($elementModuleData, $name);
        $returnArray[$type][$name] = $elementModuleData;
        $this->save($this->modulesCachePath, (object)$returnArray);
    }

    public function installUpdateModuleAction(): void
    {
        if (!isset($_REQUEST['installModule'], $_REQUEST['directoryName'], $_REQUEST['type']) || !$this->verifyFormActions(true)) {
            return;
        }
        $url = $_REQUEST['installModule'];
        $folderName = $_REQUEST['directoryName'];
        $type = $_REQUEST['type'];
        $path = sprintf('%s/%s/', $GLOBALS['APP_DIR'], $type);
        if (in_array($type, [$GLOBALS['FILES_DIR'], $GLOBALS['PLUGINS_DIR']], true)) {
            $zipFile = $this->filesPath . '/ZIPFromURL.zip';
            $zipResource = fopen($zipFile, 'w');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FILE, $zipResource);
            curl_exec($ch);
            $curlError = curl_error($ch);
            curl_close($ch);
            $zip = new ZipArchive;
            if ($curlError || $zip->open($zipFile) !== true || (stripos($url, '.zip') === false)) {
                $this->recursiveDelete($GLOBALS['APP_DIR'] . '/data/files/ZIPFromURL.zip');
                $this->alert('danger', 'Error opening ZIP file.' . ($curlError ? ' Error description: ' . $curlError : ''));
                $this->redirect();
            }
            $this->recursiveDelete($path . $folderName);
            $zip->extractTo($path);
            $zip->close();
            $this->recursiveDelete($GLOBALS['APP_DIR'] . '/data/files/ZIPFromURL.zip');
            $moduleFolder = $path . $folderName . '-master';
            if (!is_dir($moduleFolder)) {
                $moduleFolder = $path . $folderName . '-main';
            }
            if (is_dir($moduleFolder) && !rename($moduleFolder, $path . $folderName)) {
                throw new Exception('Files or plugin not installed. Possible cause: files or plugins folder is not writable.');
            }
            $this->alert('success', 'Successfully installed/updated ' . $folderName . '.');
            $this->redirect();
        }
    }

    public function recursiveDelete(string $file): void
    {
        if (is_dir($file)) {
            $files = new DirectoryIterator($file);
            foreach ($files as $dirFile) {
                if (!$dirFile->isDot()) {
                    $dirFile->isDir() ? $this->recursiveDelete($dirFile->getPathname()) : unlink($dirFile->getPathname());
                }
            }
            rmdir($file);
        } elseif (is_file($file)) {
            unlink($file);
        }
    }

    public function changePasswordAction(): void
    {
        if (isset($_POST['old_password'], $_POST['new_password'], $_POST['repeat_password']) && $_SESSION['token'] === $_POST['token'] && $this->loggedIn && $this->hashVerify($_POST['token'])) {
            if (!password_verify($_POST['old_password'], $this->get('config', 'password'))) {
                $this->alert('danger', 'Wrong password. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#security"><b>Re-open security options</b></a>');
                $this->redirect();
                return;
            }
            if (strlen($_POST['new_password']) < $GLOBALS['MIN_PASSWORD_LENGTH']) {
                $this->alert('danger', sprintf('Password must be longer than %d characters. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#security"><b>Re-open security options</b></a>', $GLOBALS['MIN_PASSWORD_LENGTH']));
                $this->redirect();
                return;
            }
            if ($_POST['new_password'] !== $_POST['repeat_password']) {
                $this->alert('danger', 'New passwords do not match. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#security"><b>Re-open security options</b></a>');
                $this->redirect();
                return;
            }
            $this->set('config', 'password', password_hash($_POST['new_password'], PASSWORD_DEFAULT));
            $this->set('config', 'forceLogout', true);
            $this->logoutAction(true);
            $this->alert('success', '<div style="text-align: center;"><b>Password changed. Log in again.</b></div>', 1);
        }
    }

    public function deleteFileModuleAction(): void
    {
        if (!$this->loggedIn) {
            return;
        }
        if (isset($_REQUEST['deleteModule'], $_REQUEST['type']) && $this->verifyFormActions(true)) {
            $allowedDeleteTypes = ['files', 'plugins', 'files'];
            $filename = str_ireplace(['/', './', '../', '..', '~', '~/', '\\'], null, trim($_REQUEST['deleteModule']));
            $type = str_ireplace(['/', './', '../', '..', '~', '~/', '\\'], null, trim($_REQUEST['type']));
            if (!in_array($type, $allowedDeleteTypes, true)) {
                $this->alert('danger', 'Wrong delete folder path.');
                $this->redirect();
            }
            if ($filename === $this->get('config', 'files')) {
                $this->alert('danger', 'Cannot delete currently active files. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>Re-open files options</b></a>');
                $this->redirect();
            }
            $folder = $type === 'files' ? $this->filesPath : sprintf('%s/%s', $GLOBALS['APP_DIR'], $type);
            $path = realpath("{$folder}/{$filename}");
            if (file_exists($path)) {
                $this->recursiveDelete($path);
                $this->alert('success', "Deleted {$filename}.");
                $this->redirect();
            }
        }
    }

    public function changeSiteFilesAction(): void
    {
        if (isset($_REQUEST['selectModule'], $_REQUEST['type']) && $this->verifyFormActions(true)) {
            $files = $_REQUEST['selectModule'];
            if (!is_dir($GLOBALS['APP_DIR'] . '/' . $_REQUEST['type'] . '/' . $files)) {
                return;
            }
            $this->set('config', 'files', $files);
            $this->redirect();
        }
    }

    public function backupAction(): void
    {
        if (!$this->loggedIn) {
            return;
        }
        $backupList = glob($this->filesPath . '/*-backup-*.zip');
        if (!empty($backupList)) {
            $this->alert('danger', 'Backup files detected. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>View and delete unnecessary backup files</b></a>');
        }
        if (isset($_POST['backup']) && $this->verifyFormActions()) {
            $this->zipBackup();
        }
    }

    public function zipBackup(): void
    {
        try {
            $randomNumber = random_bytes(8);
        } catch (Exception $e) {
            $randomNumber = microtime(false);
        }
        $zipName = date('Y-m-d') . '-backup-' . bin2hex($randomNumber) . '.zip';
        $zipPath = $GLOBALS['APP_DIR'] . '/data/files/' . $zipName;
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            $this->alert('danger', 'Cannot create ZIP archive.');
        }
        $iterator = new RecursiveDirectoryIterator($GLOBALS['APP_DIR']);
        $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST);
        foreach ($files as $file) {
            $file = realpath($file);
            $source = realpath($GLOBALS['APP_DIR']);
            if (is_dir($file)) {
                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
            } elseif (is_file($file)) {
                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
            }
        }
        $zip->close();
        $this->redirect('data/files/' . $zipName);
    }

    public function forceHttpsAction(): void
    {
        if (isset($_POST['forceHttps']) && $this->verifyFormActions()) {
            $this->set('config', 'forceHttps', $_POST['forceHttps'] === 'true');
            $this->updateSecurityCache();
            $this->alert('success', 'Force HTTPs was successfully changed.');
            $this->redirect();
        }
    }

    public function saveChangesPopupAction(): void
    {
        if (isset($_POST['saveChangesPopup']) && $this->verifyFormActions()) {
            $this->set('config', 'saveChangesPopup', $_POST['saveChangesPopup'] === 'true');
            $this->alert('success', 'Saving the confirmation popup options changed.');
            $this->redirect();
        }
    }

    public function deleteSiteAction(): void
    {
        if (!isset($_GET['delete']) || !$this->verifyFormActions(true)) {
            return;
        }
        $slugTree = explode('/', $_GET['delete']);
        $this->deleteSiteFromDb($slugTree);
        $allNavigationItems = $selectedNavigationItem = clone $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        if (count(get_object_vars($allNavigationItems)) === 1) {
            $this->alert('danger', 'Last site cannot be deleted - at least one site must exist.');
            $this->redirect();
        }
        $selectedNavigationItemParent = $selectedNavigationItemKey = null;
        foreach ($slugTree as $slug) {
            $selectedNavigationItemParent = $selectedNavigationItem->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} ?? $selectedNavigationItem;
            foreach ($selectedNavigationItemParent as $navigationItemKey => $navigationItem) {
                if ($navigationItem->slug === $slug) {
                    $selectedNavigationItem = $navigationItem;
                    $selectedNavigationItemKey = $navigationItemKey;
                    break;
                }
            }
        }
        unset($selectedNavigationItemParent->{$selectedNavigationItemKey});
        $allNavigationItems = $this->reindexObject($allNavigationItems);
        $defaultSite = $this->get($GLOBALS['DB_CONFIG'], 'defaultSite');
        $defaultSiteArray = explode('/', $defaultSite);
        $treeIntersect = array_intersect_assoc($defaultSiteArray, $slugTree);
        if ($treeIntersect === $slugTree) {
            $this->set($GLOBALS['DB_CONFIG'], 'defaultSite', $allNavigationItems->{0}->slug);
        }
        $this->set($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'], $allNavigationItems);
        $this->alert('success', 'Site <b>' . $slug . '</b> deleted.');
        $this->redirect();
    }

    public function deleteSiteFromDb(array $slugTree = null): void
    {
        $slug = array_pop($slugTree);
        $selectedSite = $this->db->{$GLOBALS['DB_SITES_KEY']};
        if (!empty($slugTree)) {
            foreach ($slugTree as $childSlug) {
                $selectedSite = $selectedSite->{$childSlug}->subsites;
            }
        }
        unset($selectedSite->{$slug});
    }

    private function reindexObject(stdClass $object): stdClass
    {
        $reindexObject = new stdClass;
        $index = 0;
        foreach ($object as $value) {
            $reindexObject->{$index} = $value;
            $index++;
        }
        return $reindexObject;
    }

    public function saveAction(): void
    {
        if (!$this->loggedIn) {
            return;
        }
        if (isset($_SESSION['redirect_to'])) {
            $newUrl = $_SESSION['redirect_to'];
            $newSiteName = $_SESSION['redirect_to_name'];
            unset($_SESSION['redirect_to'], $_SESSION['redirect_to_name']);
            $this->alert('success', "Site <b>$newSiteName</b> created. Click <a href=" . $newUrl . ">here</a> to open it.");
            $this->redirect($newUrl);
        }
        if (isset($_POST['fieldname'], $_POST['content'], $_POST['target'], $_POST['token']) && $this->hashVerify($_POST['token'])) {
            [$fieldname, $content, $target, $navigation, $visibility] = $this->hook('save', $_POST['fieldname'], $_POST['content'], $_POST['target'], $_POST['navigation'], ($_POST['visibility'] ?? 'hide'));
            if ($target === 'navigationItemUpdate') {
                $this->updateNavigationItem($content, $navigation, $visibility);
                $_SESSION['redirect_to_name'] = $content;
                $_SESSION['redirect_to'] = $this->slugify($content);
            }
            if ($target === 'navigationItemCreate') {
                $this->createNavigationItem($content, $navigation, $visibility, true);
            }
            if ($target === 'navigationItemVsbl') {
                $this->updateNavigationItemVisibility($visibility, $navigation);
            }
            if ($target === 'navigationItemOrder') {
                $this->orderNavigationItem($content, $navigation);
            }
            if ($fieldname === 'defaultSite' && $this->getSiteData($content) === null) {
                return;
            }
            if ($fieldname === 'login' && (empty($content) || $this->getSiteData($content) !== null)) {
                return;
            }
            if ($fieldname === 'files' && !is_dir($GLOBALS['APP_DIR'] . '/files/' . $content)) {
                return;
            }
            if ($target === 'config') {
                $this->set('config', $fieldname, $content);
            } elseif ($target === 'widgets') {
                $this->set('widgets', $fieldname, 'content', $content);
            } elseif ($target === 'sites') {
                if (!$this->currentSiteExists) {
                    $this->createSite($this->currentSiteTree, true);
                }
                $this->updateSite($this->currentSiteTree, $fieldname, $content);
            }
        }
    }

    public function hook(): array
    {
        $numArgs = func_num_args();
        $args = func_get_args();
        if ($numArgs < 2) {
            trigger_error('Insufficient arguments', E_USER_ERROR);
        }
        $hookName = array_shift($args);
        if (!isset($this->listeners[$hookName])) {
            return $args;
        }
        foreach ($this->listeners[$hookName] as $func) {
            $args = $func($args);
        }
        return $args;
    }

    public function updateNavigationItem(string $name, string $navigation, string $visibility = 'hide'): void
    {
        if (!in_array($visibility, ['show', 'hide'], true)) {
            return;
        }
        $name = empty($name) ? 'empty' : str_replace([PHP_EOL, '<br>'], '', $name);
        $slug = $this->createUniqueSlug($name, $navigation);
        $navigationItems = $navigationSelectionObject = clone $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        $navigationTree = explode('-', $navigation);
        $slugTree = [];
        $navigationKey = array_pop($navigationTree);
        if (count($navigationTree) > 0) {
            foreach ($navigationTree as $childNavigationKey) {
                $childNavigation = $navigationSelectionObject->{$childNavigationKey};
                if (!property_exists($childNavigation, $GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE'])) {
                    $childNavigation->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} = new stdClass;
                }
                $navigationSelectionObject = $childNavigation->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']};
                $slugTree[] = $childNavigation->slug;
            }
        }
        $slugTree[] = $navigationSelectionObject->{$navigationKey}->slug;
        $navigationSelectionObject->{$navigationKey}->name = $name;
        $navigationSelectionObject->{$navigationKey}->slug = $slug;
        $navigationSelectionObject->{$navigationKey}->visibility = $visibility;
        $navigationSelectionObject->{$navigationKey}->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} = $navigationSelectionObject->{$navigationKey}->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} ?? new stdClass;
        $this->set($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'], $navigationItems);
        $this->updateSiteSlug($slugTree, $slug);
        if ($this->get($GLOBALS['DB_CONFIG'], 'defaultSite') === implode('/', $slugTree)) {
            array_pop($slugTree);
            $slugTree[] = $slug;
            $this->set($GLOBALS['DB_CONFIG'], 'defaultSite', implode('/', $slugTree));
        }
    }

    public function createUniqueSlug(string $slug, string $navigation = null): string
    {
        $slug = $this->slugify($slug);
        $allNavigationItems = $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        $navigationCount = count(get_object_vars($allNavigationItems));
        $navigationTree = $navigation ? explode('-', $navigation) : [];
        if (count($navigationTree)) {
            foreach ($navigationTree as $childNavigationKey) {
                $allNavigationItems = $allNavigationItems->{$childNavigationKey}->subsites;
            }
        }
        foreach ($allNavigationItems as $value) {
            if ($value->slug === $slug) {
                $slug .= '-' . $navigationCount;
                break;
            }
        }
        return $slug;
    }

    public function updateSiteSlug(array $slugTree, string $newSlugName): void
    {
        $slug = array_pop($slugTree);
        $selectedSite = $this->db->{$GLOBALS['DB_SITES_KEY']};
        if (!empty($slugTree)) {
            foreach ($slugTree as $childSlug) {
                $selectedSite = $selectedSite->{$childSlug}->subsites;
            }
        }
        $selectedSite->{$newSlugName} = $selectedSite->{$slug};
        unset($selectedSite->{$slug});
        $this->save();
    }

    public function createNavigationItem(string $name, string $navigation = null, string $visibility = 'hide', bool $createSite = false): void
    {
        if (!in_array($visibility, ['show', 'hide'], true)) {
            return;
        }
        $name = empty($name) ? 'empty' : str_replace([PHP_EOL, '<br>'], '', $name);
        $slug = $this->createUniqueSlug($name, $navigation);
        $navigationItems = $navigationSelectionObject = clone $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        $navigationTree = !empty($navigation) || $navigation === '0' ? explode('-', $navigation) : [];
        $slugTree = [];
        if (count($navigationTree)) {
            foreach ($navigationTree as $childNavigationKey) {
                $childNavigation = $navigationSelectionObject->{$childNavigationKey};
                if (!property_exists($childNavigation, $GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE'])) {
                    $childNavigation->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} = new stdClass;
                }
                $navigationSelectionObject = $childNavigation->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']};
                $slugTree[] = $childNavigation->slug;
            }
        }
        $slugTree[] = $slug;
        $navigationCount = count(get_object_vars($navigationSelectionObject));
        $navigationSelectionObject->{$navigationCount} = new stdClass;
        $navigationSelectionObject->{$navigationCount}->name = $name;
        $navigationSelectionObject->{$navigationCount}->slug = $slug;
        $navigationSelectionObject->{$navigationCount}->visibility = $visibility;
        $navigationSelectionObject->{$navigationCount}->{$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']} = new stdClass;
        $this->set($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'], $navigationItems);
        if ($createSite) {
            $this->createSite($slugTree);
            $_SESSION['redirect_to_name'] = $name;
            $_SESSION['redirect_to'] = implode('/', $slugTree);
        }
    }

    public function createSite(array $slugTree = null, bool $createNavigationItem = false): void
    {
        $siteExists = false;
        $siteData = null;
        foreach ($slugTree as $parentSite) {
            if (!$siteData) {
                $siteData = $this->get($GLOBALS['DB_SITES_KEY'])->{$parentSite};
                continue;
            }
            $siteData = $siteData->subsites->{$parentSite} ?? null;
            $siteExists = !empty($siteData);
        }
        if ($siteExists) {
            $this->alert('danger', 'Cannot create site with existing slug.');
            return;
        }
        $slug = array_pop($slugTree);
        $siteSlug = $slug ?: $this->slugify($this->currentSite);
        $allSites = $selectedSite = clone $this->get($GLOBALS['DB_SITES_KEY']);
        $navigationKey = null;
        if (!empty($slugTree)) {
            foreach ($slugTree as $childSlug) {
                if ($createNavigationItem) {
                    $navigationKey = $this->findAndUpdateNavigationKey($navigationKey, $childSlug);
                }
                if (!$selectedSite->{$childSlug}) {
                    $parentTitle = mb_convert_case(str_replace('-', ' ', $childSlug), MB_CASE_TITLE);
                    $selectedSite->{$childSlug}->title = $parentTitle;
                    $selectedSite->{$childSlug}->keywords = 'Keywords, are, good, for, search, engines';
                    $selectedSite->{$childSlug}->description = 'A short description is also good.';
                    if ($createNavigationItem) {
                        $this->createNavigationItem($parentTitle, $navigationKey);
                        $navigationKey = $this->findAndUpdateNavigationKey($navigationKey, $childSlug);
                    }
                }
                if (!property_exists($selectedSite->{$childSlug}, $GLOBALS['DB_SITES_SUBSITE_KEY'])) {
                    $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']} = new stdClass;
                }
                $selectedSite = $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']};
            }
        }
        $title = !$slug ? str_replace('-', ' ', $siteSlug) : $siteSlug;
        $selectedSite->{$slug} = new stdClass;
        $selectedSite->{$slug}->title = mb_convert_case($title, MB_CASE_TITLE);
        $selectedSite->{$slug}->keywords = 'Keywords, are, good, for, search, engines';
        $selectedSite->{$slug}->description = 'A short description is also good.';
        $selectedSite->{$slug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']} = new stdClass;
        $this->set($GLOBALS['DB_SITES_KEY'], $allSites);
        if ($createNavigationItem) {
            $this->createNavigationItem($title, $navigationKey);
        }
    }

    private function findAndUpdateNavigationKey(?string $navigationKey, string $slug): string
    {
        $navigationKeys = $navigationKey !== null ? explode('-', $navigationKey) : $navigationKey;
        $navigationItems = json_decode(json_encode($this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'])), true);
        foreach ($navigationKeys as $key) {
            $navigationItems = $navigationItems[$key][$GLOBALS['DB_NAVIGATION_ITEMS_SUBSITE']] ?? [];
        }
        if (false !== ($index = array_search($slug, array_column($navigationItems, 'slug'), true))) {
            $navigationKey = $navigationKey === null ? $index : $navigationKey . '-' . $index;
        } elseif ($navigationKey === null) {
            $navigationKey = count($navigationItems);
        }
        return $navigationKey;
    }

    public function updateNavigationItemVisibility(string $visibility, string $navigation): void
    {
        if (!in_array($visibility, ['show', 'hide'], true)) {
            return;
        }
        $navigationTree = explode('-', $navigation);
        $navigationItems = $navigationSelectionObject = clone $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        if ($navigationTree) {
            $mainParentNavigation = array_shift($navigationTree);
            $navigationSelectionObject = $navigationItems->{$mainParentNavigation};
            foreach ($navigationTree as $childNavigationKey) {
                $navigationSelectionObject = $navigationSelectionObject->subsites->{$childNavigationKey};
            }
        }
        $navigationSelectionObject->visibility = $visibility;
        $this->set($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'], $navigationItems);
    }

    public function orderNavigationItem(int $content, string $navigation): void
    {
        if (!in_array($content, [1, -1], true)) {
            return;
        }
        $navigationTree = $navigation ? explode('-', $navigation) : null;
        $mainParentNavigation = $selectedNavigationKey = array_shift($navigationTree);
        $navigationItems = $navigationSelectionObject = clone $this->get($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS']);
        if ($navigationTree) {
            $selectedNavigationKey = array_pop($navigationTree);
            $navigationSelectionObject = $navigationItems->{$mainParentNavigation}->subsites;
            foreach ($navigationTree as $childNavigationKey) {
                $navigationSelectionObject = $navigationSelectionObject->{$childNavigationKey}->subsites;
            }
        }
        $targetPosition = $selectedNavigationKey + $content;
        $selectedNavigation = $navigationSelectionObject->{$selectedNavigationKey};
        $targetNavigation = $navigationSelectionObject->{$targetPosition};
        $navigationSelectionObject->{$selectedNavigationKey} = $targetNavigation;
        $navigationSelectionObject->{$targetPosition} = $selectedNavigation;
        $this->set($GLOBALS['DB_CONFIG'], $GLOBALS['DB_NAVIGATION_ITEMS'], $navigationItems);
    }

    public function updateSite(array $slugTree, string $fieldname, string $content): void
    {
        $slug = array_pop($slugTree);
        $allSites = $selectedSite = clone $this->get($GLOBALS['DB_SITES_KEY']);
        if (!empty($slugTree)) {
            foreach ($slugTree as $childSlug) {
                if (!property_exists($selectedSite->{$childSlug}, $GLOBALS['DB_SITES_SUBSITE_KEY'])) {
                    $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']} = new stdClass;
                }
                $selectedSite = $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']};
            }
        }
        $selectedSite->{$slug}->{$fieldname} = $content;
        $this->set($GLOBALS['DB_SITES_KEY'], $allSites);
    }

    public function updateAction(): void
    {
        if (!isset($_POST['update']) || !$this->verifyFormActions()) {
            return;
        }
        $contents = $this->getFileFromRepo('index.php');
        if ($contents) {
            file_put_contents(__FILE__, $contents);
            $this->alert('success', 'element successfully updated. Wohoo!');
            $this->redirect();
        }
        $this->alert('danger', 'Something went wrong. Could not update element.');
        $this->redirect();
    }

    public function uploadFileAction(): void
    {
        if (!isset($_FILES['uploadFile']) || !$this->verifyFormActions()) {
            return;
        }
        $allowedMimeTypes = ['video/avi', 'text/css', 'text/x-asm', 'application/vnd.ms-word', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'video/x-flv', 'image/gif', 'text/html', 'image/x-icon', 'image/jpeg', 'application/octet-stream', 'audio/mp4', 'video/x-matroska', 'video/quicktime', 'audio/mpeg', 'video/mp4', 'video/mpeg', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.text', 'application/ogg', 'video/ogg', 'application/pdf', 'image/png', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'application/photoshop', 'application/rar', 'image/svg', 'image/svg+xml', 'application/svg+xm', 'text/plain', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'video/webm', 'video/x-ms-wmv', 'application/zip',];
        $allowedExtensions = ['avi', 'css', 'doc', 'docx', 'flv', 'gif', 'htm', 'html', 'ico', 'jpg', 'kdbx', 'm4a', 'mkv', 'mov', 'mp3', 'mp4', 'mpg', 'ods', 'odt', 'ogg', 'ogv', 'pdf', 'png', 'ppt', 'pptx', 'psd', 'rar', 'svg', 'txt', 'xls', 'xlsx', 'webm', 'wmv', 'zip',];
        if (!isset($_FILES['uploadFile']['error']) || is_array($_FILES['uploadFile']['error'])) {
            $this->alert('danger', 'Invalid parameters.');
            $this->redirect();
        }
        switch ($_FILES['uploadFile']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $this->alert('danger', 'No file selected. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>Re-open file options</b></a>');
                $this->redirect();
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $this->alert('danger', 'File too large. Change maximum upload size limit or contact your host. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>Re-open file options</b></a>');
                $this->redirect();
                break;
            default:
                $this->alert('danger', 'Unknown error.');
                $this->redirect();
        }
        $mimeType = '';
        $fileName = basename(str_replace(['"', "'", '*', '<', '>', '%22', '&#39;', '%', ';', '#', '&', './', '../', '/', '+'], '', filter_var($_FILES['uploadFile']['name'], FILTER_SANITIZE_STRING)));
        $nameExploded = explode('.', $fileName);
        $ext = strtolower(array_pop($nameExploded));
        if (class_exists('finfo')) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($_FILES['uploadFile']['tmp_name']);
        } elseif (function_exists('mime_content_type')) {
            $mimeType = mime_content_type($_FILES['uploadFile']['tmp_name']);
        } elseif (array_key_exists($ext, $allowedExtensions)) {
            $mimeType = $allowedExtensions[$ext];
        }
        if (!in_array($mimeType, $allowedMimeTypes, true) || !in_array($ext, $allowedExtensions)) {
            $this->alert('danger', 'File format is not allowed. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>Re-open file options</b></a>');
            $this->redirect();
        }
        if (!move_uploaded_file($_FILES['uploadFile']['tmp_name'], $this->filesPath . '/' . $fileName)) {
            $this->alert('danger', 'Failed to move uploaded file.');
        }
        $this->alert('success', 'File uploaded. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#files"><b>Open file options to see your uploaded file</b></a>');
        $this->redirect();
    }

    public function notifyAction(): void
    {
        if (!$this->loggedIn) {
            return;
        }
        if (!$this->currentSiteExists) {
            $this->alert('info', '<b>This site (' . $this->currentSite . ') doesn\'t exist.</b> Editing the content below will create it.');
        }
        if ($this->get('config', 'login') === 'cms') {
            $this->alert('danger', 'Change your login URL and save it for later use. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#security"><b>Open security options</b></a>');
        }
        $this->checkModulesCache();
    }

    public function render(): void
    {
        header($this->headerResponse);
        if ($this->loggedIn) {
            $loadingSite = null;
            foreach ($this->get('config', 'navigationItems') as $item) {
                if ($this->currentSite === $item->slug) {
                    $loadingSite = $item;
                }
            }
            if ($loadingSite && $loadingSite->visibility === 'hide') {
                $this->alert('info', 'This site (' . $this->currentSite . ') is currently hidden from the navigation. <a data-toggle="element-modal" href="#optionsModal" data-target-tab="#navigation"><b>Open navigation visibility options</b></a>');
            }
        }
        $this->loadFilesAndFunctions();
    }

    public function loadFilesAndFunctions(): void
    {
        $location = $GLOBALS['APP_DIR'] . '/files/' . $this->get('config', 'files');
        if (file_exists($location . '/functions.php')) {
            require_once $location . '/functions.php';
        }
        $customSiteTemplate = sprintf('%s/%s.php', $location, $this->currentSite);
        require_once file_exists($customSiteTemplate) ? $customSiteTemplate : $location . '/index.php';
    }

    public function addListener(string $hook, callable $functionName): void
    {
        $this->listeners[$hook][] = $functionName;
    }

    public function notice(): string
    {
        if (!isset($_SESSION['alert'])) {
            return '';
        }
        $output = '';
        $output .= '<div id="alertWrapperId" class="alertWrapper" style="">';
        $output .= '<script>
					const displayNotice = localStorage.getItem("displayNotice");
					if (displayNotice === "false") {
						const alertWrapper = document.getElementById("alertWrapperId");
						if (alertWrapper) {
							alertWrapper.style.display = "none";
						}
					}
					</script>';
        foreach ($_SESSION['alert'] as $alertClass) {
            foreach ($alertClass as $alert) {
                $output .= '<div class="alert alert-' . $alert['class'] . (!$alert['sticky'] ? ' alert-dismissible' : '') . '">' . (!$alert['sticky'] ? '<button type="button" class="close" data-dismiss="alert" onclick="parentNode.remove();">&times;</button>' : '') . $alert['message'] . $this->hideNotice();
            }
        }
        $output .= '</div>';
        unset($_SESSION['alert']);
        return $output;
    }

    public function hideNotice(): string
    {
        if (!$this->loggedIn) {
            return '';
        }
        $output = '';
        $output .= '<br><a href="" onclick="localStorage.setItem(\'displayNotice\', \'false\');"><small>Hide all notice until next login</small></a></div>';
        return $output;
    }

    public function resource(string $location): string
    {
        return self::url('files/' . $this->get('config', 'files') . '/' . $location);
    }

    public function widget(string $key): string
    {
        $widgets = $this->get('widgets');
        $content = '';
        if (isset($widgets->{$key})) {
            $content = $this->loggedIn ? $this->editable($key, $widgets->{$key}->content, 'widgets') : $widgets->{$key}->content;
        }
        return $this->hook('widget', $content, $key)[0];
    }

    public function editable(string $id, string $content, string $dataTarget = ''): string
    {
        return '<div' . ($dataTarget !== '' ? ' data-target="' . $dataTarget . '"' : '') . ' id="' . $id . '" class="editText editable">' . $content . '</div>';
    }

    public function deleteSiteKey(array $slugTree, string $fieldname): void
    {
        $slug = array_pop($slugTree);
        $selectedSite = clone $this->get($GLOBALS['DB_SITES_KEY']);
        if (!empty($slugTree)) {
            foreach ($slugTree as $childSlug) {
                if (!property_exists($selectedSite->{$childSlug}, $GLOBALS['DB_SITES_SUBSITE_KEY'])) {
                    $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']} = new stdClass;
                }
                $selectedSite = $selectedSite->{$childSlug}->{$GLOBALS['DB_SITES_SUBSITE_KEY']};
            }
        }
        unset($selectedSite->{$slug}->{$fieldname});
    }

    public function css(): string
    {
        if ($this->loggedIn) {
            $styles = <<<'EOT'
<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/donPabloNow/element-cdn-files@3.2.25/element-admin.min.css" crossorigin="anonymous">
EOT;
            return $this->hook('css', $styles)[0];
        }
        return $this->hook('css', '')[0];
    }

    public function title(): string
    {
        $output = $this->get('config', 'title');
        if ($this->loggedIn) {
            $output .= "<a data-toggle='element-modal' href='#optionsModal' data-target-tab='#navigation'><i class='editIcon'></i></a>";
        }
        return $output;
    }

    public function footer(): string
    {
        if ($this->loggedIn) {
            $output = '<div data-target="widgets" id="footer" class="editText editable">' . $this->get('widgets', 'footer')->content . '</div>';
        } else {
            $output = $this->get('widgets', 'footer')->content . (!$this->loggedIn && $this->get('config', 'login') === 'cms' ? ' &bull; <a href="' . self::url('cms') . '">Login</a>' : '');
        }
        return $this->hook('footer', $output)[0];
    }

    public function js(): string
    {
        if ($this->loggedIn) {
            $scripts = <<<EOT
<script src="https://cdn.jsdelivr.net/npm/autosize@4.0.2/dist/autosize.min.js" integrity="sha384-gqYjRLBp7SeF6PCEz2XeqqNyvtxuzI3DuEepcrNHbrO+KG3woVNa/ISn/i8gGtW8" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/taboverride@4.0.3/build/output/taboverride.min.js" integrity="sha384-fYHyZra+saKYZN+7O59tPxgkgfujmYExoI6zUvvvrKVT1b7krdcdEpTLVJoF/ap1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/gh/donPabloNow/element-cdn-files@3.2.26/element-admin.min.js" integrity="sha384-lwdbkm/17hWy+Y4iBnY0iEp0FlaKvjdeTBZaRYM1DGPshGgxKoPaB87Xue26Wv1W" crossorigin="anonymous"></script>
EOT;
            $scripts .= '<script>const token = "' . $this->getToken() . '";</script>';
            $scripts .= '<script>const rootURL = "' . $this->url() . '";</script>';
            return $this->hook('js', $scripts)[0];
        }
        return $this->hook('js', '')[0];
    }

    public function navigation(): string
    {
        $output = '';
        foreach ($this->get('config', 'navigationItems') as $item) {
            if ($item->visibility === 'hide') {
                continue;
            }
            $output .= $this->renderSiteNavNavigationItem($item);
        }
        return $this->hook('navigation', $output)[0];
    }

    private function renderSiteNavNavigationItem(object $item, string $parentSlug = ''): string
    {
        $subsites = $visibleSubsite = false;
        if (property_exists($item, 'subsites') && !empty((array)$item->subsites)) {
            $subsites = $item->subsites;
            $visibleSubsite = $subsites && in_array('show', array_column((array)$subsites, 'visibility'));
        }
        $parentSlug .= $subsites ? $item->slug . '/' : $item->slug;
        $output = '<li class="nav-item ' . ($this->currentSite === $item->slug ? 'active ' : '') . ($visibleSubsite ? 'subsite-nav' : '') . '">
						<a class="nav-link" href="' . self::url($parentSlug) . '">' . $item->name . '</a>';
        if ($visibleSubsite) {
            $output .= '<ul class="subSiteDropdown">';
            foreach ($subsites as $subsite) {
                if ($subsite->visibility === 'hide') {
                    continue;
                }
                $output .= $this->renderSiteNavNavigationItem($subsite, $parentSlug);
            }
            $output .= '</ul>';
        }
        $output .= '</li>';
        return $output;
    }

    public function site(string $key): string
    {
        $segments = $this->getCurrentSiteData();
        if (!$this->currentSiteExists || !$segments) {
            $segments = $this->get('config', 'login') === $this->currentSite ? (object)$this->loginView() : (object)$this->notFoundView();
        }
        $segments->content = $segments->content ?? '<h2>Click here add content</h2>';
        $keys = ['title' => $segments->title, 'description' => $segments->description, 'keywords' => $segments->keywords, 'content' => $this->loggedIn ? $this->editable('content', $segments->content, 'sites') : $segments->content];
        $content = $keys[$key] ?? '';
        return $this->hook('site', $content, $key)[0];
    }

    public function loginView(): array
    {
        return ['title' => 'Login', 'description' => '', 'keywords' => '', 'content' => '
			<style>.showUpdate{display: block !important}</style>
				<div class="wUpdate" style="display:none;color:#ccc;left:0;top:0;width:100%;height:100%;position:fixed;text-align:center;padding-top:100px;background:rgba(51,51,51,.8);z-index:2448"><h2>Logging in and checking for updates</h2><p>This might take a moment.</p></div>
				<form action="' . self::url($this->get('config', 'login')) . '" method="post">
					<div class="winput-group text-center">
						<h1>Login to your website</h1>
						<input type="password" class="wform-control" id="password" name="password" placeholder="Password" autofocus><br><br>
						<span class="winput-group-btn">
							<button type="submit" class="wbtn wbtn-info" onclick="document.getElementsByClassName(\'wUpdate\')[0].classList.toggle(\'showUpdate\'); localStorage.clear();">Login</button>
						</span>
					</div>
				</form>'];
    }

    public function notFoundView()
    {
        if ($this->loggedIn) {
            return ['title' => str_replace('-', ' ', $this->currentSite), 'description' => '', 'keywords' => '', 'content' => '<h2>Click to create content</h2>'];
        }
        return $this->get('sites', '404');
    }

    public function options(): string
    {
        if (!$this->loggedIn) {
            return '';
        }
        $currentSiteData = $this->getCurrentSiteData();
        $fileList = array_slice(scandir($this->filesPath), 2);
        $output = '
		<script>var saveChangesPopup = ' . ($this->isSaveChangesPopupEnabled() ? "true" : "false") . '</script>
		<div id="save" class="loader-overlay"><h2><i class="animationLoader"></i><br />Saving</h2></div>
		<div id="cache" class="loader-overlay"><h2><i class="animationLoader"></i><br />Checking for updates</h2></div>
		<div id="adminPanel">
			<a data-toggle="element-modal" class="wbtn wbtn-secondary wbtn-sm options button" href="#optionsModal"><i class="optionsIcon"></i> options </a> <a href="' . self::url('logout&token=' . $this->getToken()) . '" class="wbtn wbtn-danger wbtn-sm button logout" title="Logout" onclick="return confirm(\'Log out?\')"><i class="logoutIcon"></i></a>
			<div class="element-modal modal" id="optionsModal">
				<div class="modal-dialog modal-xl">
				 <div class="modal-content">
					<div class="modal-header"><button type="button" class="close" data-dismiss="element-modal" aria-hidden="true">&times;</button></div>
					<div class="modal-body coll-xs-12 coll-12">
						<ul class="nav nav-tabs justify-content-center text-center" role="tablist">
							<li role="presentation" class="nav-item"><a href="#currentSite" aria-controls="currentSite" role="tab" data-toggle="tab" class="nav-link active">Current site</a></li>
							<li role="presentation" class="nav-item"><a href="#navigation" aria-controls="navigation" role="tab" data-toggle="tab" class="nav-link">Navigation</a></li>
							<li role="presentation" class="nav-item"><a href="#files" aria-controls="files" role="tab" data-toggle="tab" class="nav-link">Files</a></li>
							<li role="presentation" class="nav-item"><a href="#files" aria-controls="files" role="tab" data-toggle="tab" class="nav-link">Files</a></li>
							<li role="presentation" class="nav-item"><a href="#plugins" aria-controls="plugins" role="tab" data-toggle="tab" class="nav-link">Plugins</a></li>
							<li role="presentation" class="nav-item"><a href="#security" aria-controls="security" role="tab" data-toggle="tab" class="nav-link">Security</a></li>
						</ul>
						<div class="tab-content coll-md-8 coll-md-offset-2 offset-md-2">
							<div role="tabpanel" class="tab-pane active" id="currentSite">';
        if ($this->currentSiteExists && $currentSiteData) {
            $output .= '
								<p class="subTitle">Site title</p>
								<div class="change">
									<div data-target="sites" id="title" class="editText">' . ($currentSiteData->title ?: '') . '</div>
								</div>
								<p class="subTitle">Site keywords</p>
								<div class="change">
									<div data-target="sites" id="keywords" class="editText">' . ($currentSiteData->keywords ?: '') . '</div>
								</div>
								<p class="subTitle">Site description</p>
								<div class="change">
									<div data-target="sites" id="description" class="editText">' . ($currentSiteData->description ?: '') . '</div>
								</div>
								<a href="' . self::url('?delete=' . implode('/', $this->currentSiteTree) . '&token=' . $this->getToken()) . '" class="wbtn wbtn-danger pull-right marginTop40" title="Delete site" onclick="return confirm(\'Delete ' . $this->currentSite . '?\')"><i class="deleteIconInButton"></i> Delete site (' . $this->currentSite . ')</a>';
        } else {
            $output .= 'This site doesn\'t exist. More options will be displayed here after this site is created.';
        }
        $output .= '
							</div>
							<div role="tabpanel" class="tab-pane" id="navigation">';
        $items = $this->get('config', 'navigationItems');
        reset($items);
        $first = key($items);
        end($items);
        $end = key($items);
        $output .= '
							 <p class="subTitle">Website title</p>
							 <div class="change">
								<div data-target="config" id="title" class="editText">' . $this->get('config', 'title') . '</div>
							 </div>
							 <p class="subTitle">Navigation</p>
							 <div>
								<div id="navigationoptions" class="container-fluid">
								<a class="navigation-item-add wbtn wbtn-info cursorPointer" data-toggle="tooltip" id="navigationItemAdd" title="Add new site"><i class="addNewIcon"></i> Add site</a><br><br>';
        foreach ($items as $key => $value) {
            $output .= '<div class="row">';
            $output .= $this->renderoptionsNavigationItem($key, $value, ($key === $first), ($key === $end), $value->slug);
            if (property_exists($value, 'subsites')) {
                $output .= $this->renderoptionsSubNavigationItem($value->subsites, $key, $value->slug);
            }
            $output .= '</div>';
        }
        $output .= '			</div>
							 </div>
							 <p class="subTitle">Site to display on homesite</p>
							 <div class="change">
								<select id="changeDefaultSite" class="wform-control" name="defaultSite">';
        $items = $this->get('config', 'navigationItems');
        $defaultSite = $this->get('config', 'defaultSite');
        foreach ($items as $item) {
            $output .= $this->renderDefaultSiteOptions($item, $defaultSite);
        }
        $output .= '
								</select>
							</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="files">
							 <p class="subTitle">Upload</p>
							 <div class="change">
								<form action="' . $this->getCurrentSiteUrl() . '" method="post" enctype="multipart/form-data">
									<div class="winput-group"><input type="file" name="uploadFile" class="wform-control">
										<span class="winput-group-btn"><button type="submit" class="wbtn wbtn-info"><i class="uploadIcon"></i>Upload</button></span>
										<input type="hidden" name="token" value="' . $this->getToken() . '">
									</div>
								</form>
							 </div>
							 <p class="subTitle marginTop20">Delete files</p>
							 <div class="change">';
        foreach ($fileList as $file) {
            $output .= '
									<a href="' . self::url('?deleteModule=' . $file . '&type=files&token=' . $this->getToken()) . '" class="wbtn wbtn-sm wbtn-danger" onclick="return confirm(\'Delete ' . $file . '?\')" title="Delete file"><i class="deleteIcon"></i></a>
									<span class="marginLeft5">
										<a href="' . self::url('data/files/') . $file . '" class="normalFont" target="_blank">' . self::url('data/files/') . '<b class="fontSize21">' . $file . '</b></a>
									</span>
									<p></p>';
        }
        $output .= '
							 </div>
							</div>';
        $output .= $this->renderModuleTab();
        $output .= $this->renderModuleTab('plugins');
        $output .= '		<div role="tabpanel" class="tab-pane" id="security">
							 <p class="subTitle">Admin login URL</p>
								<p class="change marginTop5 small danger">Important: save your login URL to log in to your website next time:<br/><b><span class="normalFont">' . self::url($this->get('config', 'login')) . '</b></span>
							 <div class="change">
								<div data-target="config" id="login" class="editText">' . $this->get('config', 'login') . '</div>
							 </div>
							 <p class="subTitle">Password</p>
							 <div class="change">
								<form action="' . $this->getCurrentSiteUrl() . '" method="post">
									<input type="password" name="old_password" class="wform-control normalFont" placeholder="Old password"><br>
									<div class="winput-group">
										<input type="password" name="new_password" class="wform-control normalFont" placeholder="New password"><span class="winput-group-btn"></span>
										<input type="password" name="repeat_password" class="wform-control normalFont" placeholder="Repeat new password">
										<span class="winput-group-btn"><button type="submit" class="wbtn wbtn-info"><i class="lockIcon"></i> Change password</button></span>
									</div>
									<input type="hidden" name="fieldname" value="password"><input type="hidden" name="token" value="' . $this->getToken() . '">
								</form>
							 </div>
<p class="subTitle">Backup</p>
							 <div class="change">
								<form action="' . $this->getCurrentSiteUrl() . '" method="post">
									<button type="submit" class="wbtn wbtn-widget wbtn-info" name="backup"><i class="installIcon"></i> Backup website</button><input type="hidden" name="token" value="' . $this->getToken() . '">
								</form>
							 </div>
							 <p class="text-right marginTop5"><a href="https://github.com/donPabloNow/element/wiki/Restore-backup#how-to-restore-a-backup-in-3-steps" target="_blank"><i class="linkIcon"></i> How to restore backup</a></p>
							 
							 <p class="subTitle">Save confirmation popup</p>
							 <p class="change small">If this is turned on, element will always ask you to confirm any changes you make.</p>
							 <div class="change">
								<form method="post">
									<div class="wbtn-group wbtn-group-justified w-100">
										<div class="wbtn-group w-50"><button type="submit" class="wbtn wbtn-info" name="saveChangesPopup" value="true">ON</button></div>
										<div class="wbtn-group w-50"><button type="submit" class="wbtn wbtn-danger" name="saveChangesPopup" value="false">OFF</button></div>
									</div>
									<input type="hidden" name="token" value="' . $this->getToken() . '">
								</form>
							 </div>
							 
							 <p class="subTitle">Force HTTPS</p>
							 <p class="change small">element automatically checks for SSL, this will force to always use HTTPS.</p>
							 <div class="change">
								<form method="post">
									<div class="wbtn-group wbtn-group-justified w-100">
										<div class="wbtn-group w-50"><button type="submit" class="wbtn wbtn-info" name="forceHttps" value="true" onclick="return confirm(\'Are you sure? This might break your website if you do not have SSL configured correctly.\')">ON</button></div>
										<div class="wbtn-group w-50"><button type="submit" class="wbtn wbtn-danger" name="forceHttps" value="false">OFF</button></div>
									</div>
									<input type="hidden" name="token" value="' . $this->getToken() . '">
								</form>
							 </div>
							 <p class="text-right marginTop5"><a href="https://github.com/donPabloNow/element/wiki/Better-security-mode-(HTTPS-and-other-features)#important-read-before-turning-this-feature-on" target="_blank"><i class="linkIcon"></i> Read more before enabling</a></p>';
        $output .= $this->renderAdminLoginIPs();
        $output .= '
				 		 </div>
						</div>
					</div>
					<div class="modal-footer clear">
						<p class="small">
							<a href="https://element.com" target="_blank">element ' . BUILD . '</a> &nbsp;
							<b><a href="https://element.com/news" target="_blank">News</a> &nbsp;
							<a href="https://element.com/community" target="_blank">Community</a> &nbsp;
							<a href="https://github.com/donPabloNow/element/wiki#element-documentation" target="_blank">Docs</a> &nbsp;
							<a href="https://element.com/donate" target="_blank">Donate</a> &nbsp;
							<a href="https://swag.element.com" target="_blank">Shop/Merch</a></b>
						</p>
					</div>
				 </div>
				</div>
			</div>
		</div>';
        return $this->hook('options', $output)[0];
    }

    private function isSaveChangesPopupEnabled(): bool
    {
        return $this->get('config', 'saveChangesPopup') ?? false;
    }

    private function renderoptionsNavigationItem(string $navigationKeyTree, object $value, bool $isFirstEl, bool $isLastEl, string $slugTree): string
    {
        $arraySlugTree = explode('/', $slugTree);
        array_shift($arraySlugTree);
        $subNavigationLevel = count($arraySlugTree);
        $output = '<div class="coll-xs-2 coll-sm-1">
						<i class="navigation-toggle eyeIcon' . ($value->visibility === 'show' ? ' eyeShowIcon navigation-item-hide' : ' eyeHideIcon navigation-item-show') . '" data-toggle="tooltip" title="' . ($value->visibility === 'show' ? 'Hide site from navigation' : 'Show site in navigation') . '" data-navigation="' . $navigationKeyTree . '"></i>
					</div>
					<div class="coll-xs-4 coll-md-7">
						<div data-target="navigationItemUpdate" data-navigation="' . $navigationKeyTree . '" data-visibility="' . $value->visibility . '" id="navigationItems-' . $navigationKeyTree . '" class="editText" style="margin-right: ' . (13.1 * $subNavigationLevel) . 'px;">' . $value->name . '</div>
					</div>
					<div class="coll-xs-6 coll-md-4 text-right">';
        if (!$isFirstEl) {
            $output .= '<a class="arrowIcon upArrowIcon toolbar navigation-item-up cursorPointer" data-toggle="tooltip" data-navigation="' . $navigationKeyTree . '" data-navigation-slug="' . $value->slug . '" title="Move up"></a>';
        }
        if (!$isLastEl) {
            $output .= '<a class="arrowIcon downArrowIcon toolbar navigation-item-down cursorPointer" data-toggle="tooltip" data-navigation="' . $navigationKeyTree . '" data-navigation-slug="' . $value->slug . '" title="Move down"></a>';
        }
        $output .= '	<a class="linkIcon" href="' . self::url($slugTree) . '" title="Visit site" style="display: inline;">visit</a>
					</div>
					<div class="coll-xs-12 text-right marginTop5 marginBottom20">
						<a class="navigation-item-add wbtn wbtn-sm wbtn-info cursorPointer" data-toggle="tooltip" data-navigation="' . $navigationKeyTree . '" title="Add new sub-site"><i class="addNewIcon"></i> Add subsite</a>
						<a href="' . self::url('?delete=' . urlencode($slugTree) . '&token=' . $this->getToken()) . '" title="Delete site" class="wbtn wbtn-sm wbtn-danger" data-navigation="' . $navigationKeyTree . '" onclick="return confirm(\'Delete ' . $value->slug . '?\')"><i class="deleteIcon"></i></a>
					</div>';
        return $output;
    }

    private function renderoptionsSubNavigationItem(object $subsites, string $parentKeyTree, string $parentSlugTree): string
    {
        reset($subsites);
        $firstSubsite = key($subsites);
        end($subsites);
        $endSubsite = key($subsites);
        $output = '';
        foreach ($subsites as $subsiteKey => $subsite) {
            $keyTree = $parentKeyTree . '-' . $subsiteKey;
            $slugTree = $parentSlugTree . '/' . $subsite->slug;
            $output .= '<div class="coll-xs-offset-1 coll-xs-11">
							<div class="row marginTop5">';
            $firstElement = ($subsiteKey === $firstSubsite);
            $lastElement = ($subsiteKey === $endSubsite);
            $output .= $this->renderoptionsNavigationItem($keyTree, $subsite, $firstElement, $lastElement, $slugTree);
            if (property_exists($subsite, 'subsites')) {
                $output .= $this->renderoptionsSubNavigationItem($subsite->subsites, $keyTree, $slugTree);
            }
            $output .= '	</div>
						</div>';
        }
        return $output;
    }

    private function renderDefaultSiteOptions(object $navigationItem, string $defaultSite, string $parentSlug = '', string $parentName = ''): string
    {
        $slug = $parentSlug ? sprintf('%s/%s', $parentSlug, $navigationItem->slug) : $navigationItem->slug;
        $name = $parentName ? sprintf('%s | %s', $parentName, $navigationItem->name) : $navigationItem->name;
        $output = '<option value="' . $slug . '" ' . ($slug === $defaultSite ? 'selected' : '') . '>' . $name . '</option>';
        foreach ($navigationItem->subsites ?? [] as $subsite) {
            $output .= $this->renderDefaultSiteOptions($subsite, $defaultSite, $slug, $name);
        }
        return $output;
    }

    private function renderModuleTab(string $type = 'files'): string
    {
        $output = '<div role="tabpanel" class="tab-pane" id="' . $type . '">
					<a class="wbtn wbtn-info wbtn-sm pull-right float-right marginTop20 marginBottom20" data-loader-id="cache" href="' . self::url('?manuallyResetCacheData=true&token=' . $this->getToken()) . '" title="Check updates" onclick="localStorage.clear();"><i class="refreshIcon" aria-hidden="true"></i> Check for updates</a>
					<div class="clear"></div>
					<div class="change row custom-cards">';
        $defaultImage = '<svg style="max-width: 100%;" xmlns="http://www.w3.org/2000/svg" width="100%" height="140"><text x="50%" y="50%" font-size="18" text-anchor="middle" alignment-baseline="middle" font-family="monospace, sans-serif" fill="#ddd">No preview</text></svg>';
        $updates = $exists = $installs = '';
        foreach ($this->listAllModules($type) as $addonType => $addonModules) {
            foreach ($addonModules as $directoryName => $addon) {
                $name = $addon['name'];
                $info = $addon['summary'];
                $infoUrl = $addon['repo'];
                $currentBUILD = $addon['currentBUILD'] ? sprintf('Installed BUILD: %s', $addon['currentBUILD']) : '';
                $isFileselected = $this->get('config', 'files') === $directoryName;
                $image = $addon['image'] !== null ? '<a class="text-center center-widget" href="' . $addon['image'] . '" target="_blank"><img style="max-width: 100%; max-height: 250px;" src="' . $addon['image'] . '" alt="' . $name . '" /></a>' : $defaultImage;
                $installButton = $addon['install'] ? '<a class="wbtn wbtn-success wbtn-widget wbtn-sm" href="' . self::url('?installModule=' . $addon['zip'] . '&directoryName=' . $directoryName . '&type=' . $type . '&token=' . $this->getToken()) . '" title="Install"><i class="installIcon"></i> Install</a>' : '';
                $updateButton = !$addon['install'] && $addon['update'] ? '<a class="wbtn wbtn-info wbtn-sm wbtn-widget marginTop5" href="' . self::url('?installModule=' . $addon['zip'] . '&directoryName=' . $directoryName . '&type=' . $type . '&token=' . $this->getToken()) . '" title="Update"><i class="refreshIcon"></i> Update to ' . $addon['BUILD'] . '</a>' : '';
                $removeButton = !$addon['install'] ? '<a class="wbtn wbtn-danger wbtn-sm marginTop5" href="' . self::url('?deleteModule=' . $directoryName . '&type=' . $type . '&token=' . $this->getToken()) . '" onclick="return confirm(\'Remove ' . $name . '?\')" title="Remove"><i class="deleteIcon"></i></a>' : '';
                $inactiveFilesButton = $type === 'files' && !$addon['install'] && !$isFileselected ? '<a class="wbtn wbtn-primary wbtn-sm wbtn-widget" href="' . self::url('?selectModule=' . $directoryName . '&type=' . $type . '&token=' . $this->getToken()) . '" onclick="return confirm(\'Activate ' . $name . ' files?\')"><i class="checkmarkIcon"></i> Activate</a>' : '';
                $activeFilesButton = $type === 'files' && !$addon['install'] && $isFileselected ? '<a class="wbtn wbtn-primary wbtn-sm wbtn-widget" disabled>Active</a>' : '';
                $html = "<div class='coll-sm-4'>
							<div>
								$image
								<h4>$name</h4>
								<p class='normalFont'>$info</p>
								<p class='text-right small normalFont marginTop20'>$currentBUILD<br /><a href='$infoUrl' target='_blank'><i class='linkIcon'></i> More info</a></p>
								<div class='text-right'>$inactiveFilesButton $activeFilesButton</div>
								<div class='text-left'>$installButton</div>
								<div class='text-right'><span class='text-left bold'>$updateButton</span> <span class='text-right'>$removeButton</span></div>
							</div>
						</div>";
                switch ($addonType) {
                    case $GLOBALS['FILES_PLUGINS_TYPES']['updates']:
                        $updates .= $html;
                        break;
                    case $GLOBALS['FILES_PLUGINS_TYPES']['exists']:
                        $exists .= $html;
                        break;
                    case $GLOBALS['FILES_PLUGINS_TYPES']['installs']:
                    default:
                        $installs .= $html;
                        break;
                }
            }
        }
        $output .= $updates;
        $output .= $exists;
        $output .= $installs;
        $output .= '</div>
					<p class="subTitle">Custom module</p>
					<form action="' . $this->getCurrentSiteUrl() . '" method="post">
						<div class="wform-group">
							<div class="change winput-group marginTop5"><input type="text" name="pluginFilesUrl" class="wform-control normalFont" placeholder="Enter full URL to element-modules.json file">
								<span class="winput-group-btn"><button type="submit" class="wbtn wbtn-info" onclick="return confirm(\'Adding unknown modules can be VERY dangerous, are you sure you want to continue?\')"><i class="addNewIcon"></i> Add</button></span>
							</div>
						</div>
						<input type="hidden" name="token" value="' . $this->getToken() . '" /><input type="hidden" name="pluginFilesType" value="' . $type . '" />
					</form>
					<p class="text-right"><a href="https://github.com/donPabloNow/element/wiki/Custom-modules" target="_blank"><i class="linkIcon"></i> Read more about custom modules</a></p>
				</div>';
        return $output;
    }

    public function listAllModules(): array
    {
        $newData = [];
        if ($this->loggedIn) {
            $data = $this->getModulesCachedData($GLOBALS['FILES_DIR']);
            foreach ($data as $dirName => $addon) {
                $exists = is_dir($GLOBALS['APP_DIR'] . "/" . $GLOBALS['FILES_DIR'] . "/" . $dirName);
                $currentBUILD = $exists ? $this->getModuleBUILD($GLOBALS['FILES_DIR'], $dirName) : null;
                $newBUILD = $addon['BUILD'];
                $update = $newBUILD !== null && $currentBUILD !== null && $newBUILD > $currentBUILD;
                if ($update) {
                    $this->alert('info', 'New ' . $GLOBALS['FILES_DIR'] . ' update available. <b><a data-toggle="element-modal" href="#optionsModal" data-target-tab="#' . $GLOBALS['FILES_DIR'] . '">Open ' . $GLOBALS['FILES_DIR'] . '</a></b>');
                }
                $addonType = $exists ? $GLOBALS['FILES_PLUGINS_TYPES']['exists'] : $GLOBALS['FILES_PLUGINS_TYPES']['installs'];
                $addonType = $update ? $GLOBALS['FILES_PLUGINS_TYPES']['updates'] : $addonType;
                $newData[$addonType][$dirName] = $addon;
                $newData[$addonType][$dirName]['update'] = $update;
                $newData[$addonType][$dirName]['install'] = !$exists;
                $newData[$addonType][$dirName]['currentBUILD'] = $currentBUILD;
            }
        }
        return $newData;
    }

    public function getModuleBUILD(string $type, string $name): ?string
    {
        $BUILD = null;
        $path = sprintf('%s/%s/%s', $GLOBALS['APP_DIR'], $type, $name);
        $elementModulesPath = $path . '/element-modules.json';
        $BUILDPath = $path . '/BUILD';
        if (is_dir($path) && (is_file($elementModulesPath) || is_file($BUILDPath))) {
            if (is_file($elementModulesPath)) {
                $elementModules = json_decode(trim(file_get_contents($elementModulesPath)));
                $BUILD = $elementModules->{$type}->{$name}->BUILD;
            } else {
                $BUILD = trim(file_get_contents($BUILDPath));
            }
        }
        return $BUILD;
    }

    private function renderAdminLoginIPs(): string
    {
        $getIPs = $this->get('config', 'lastLogins') ?? [];
        $renderIPs = '';
        foreach ($getIPs as $time => $adminIP) {
            $renderIPs .= sprintf('%s - %s<br />', date('M d, Y H:i:s', strtotime($time)), $adminIP);
        }
        return '<p class="subTitle">Last 5 logins</p>
				<div class="change">
					' . $renderIPs . '
				</div>';
    }

    public function unset(): void
    {
        $numArgs = func_num_args();
        $args = func_get_args();
        switch ($numArgs) {
            case 1:
                unset($this->db->{$args[0]});
                break;
            case 2:
                unset($this->db->{$args[0]}->{$args[1]});
                break;
            case 3:
                unset($this->db->{$args[0]}->{$args[1]}->{$args[2]});
                break;
            case 4:
                unset($this->db->{$args[0]}->{$args[1]}->{$args[2]}->{$args[3]});
                break;
        }
        $this->save();
    }

    private function isValidGitURL(string $url): bool
    {
        return strpos($url, 'https://github.com/') !== false || strpos($url, 'https://gitlab.com/') !== false;
    }
}