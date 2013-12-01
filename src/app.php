<?php
require_once __DIR__.'/../vendor/autoload.php';
// Convert errors to exceptions
Symfony\Component\Debug\ErrorHandler::register();
Symfony\Component\Debug\ExceptionHandler::register();

/**
 * Set up app
 *
 */
$app = new Silex\Application();

// Set it to true to catch all errors made when still loading the config
$app['debug'] = true;

$app['cache_path'] = __DIR__.'/../cache';
$app['config_path'] = __DIR__.'/../config';

// This function is to cache all YAML files
function cache($filename, $loader) {
    global $app;
    if (!class_exists($loader)) throw new Exception('Class doesn\'t exist');
    if (!is_subclass_of($loader, 'Symfony\Component\Config\Loader\LoaderInterface')) throw new Exception('The loader should implement LoaderInterface');
    $cachePath = $app['cache_path'].'/'.$filename.'.cache';
    $configCache = new Symfony\Component\Config\ConfigCache($cachePath, true);
    if (!$configCache->isFresh()) {
        $locator = new Symfony\Component\Config\FileLocator($app['config_path']);
        $configFile = $locator->locate($filename, null, true);
        $configLoader = new $loader($locator);
        $config = $configLoader->load($configFile);

        $resources = array(new Symfony\Component\Config\Resource\FileResource($configFile));
        $code = '<?php return ' . var_export($config, true) . ";\n";
        $configCache->write($code, $resources);
    }
    return require $cachePath;
}

/**
 * Register error handler]
 * ========================================================================
 */
$app->error(function (Exception $e, $code) use ($app) {
    if ($app['debug']) return;
    if ($app['twig']) {
        return $app['twig']->render('error.html.twig', array('code' => $code, 'error' => $e));
    }
    return 'An error occured: '  . $code . ' ' . $e->getMessage();

    return new Symfony\Component\HttpFoundation\Response($message);
});

/**
 * Config file caching
 * ========================================================================
 */
$app['user_config'] = cache('config.yml', 'CCV\Configuration\YamlConfigLoader');

$app['debug'] = $app['user_config']['debug'];

/**
 * Registering service providers
 * ========================================================================
 */
$app->register(new Silex\Provider\MonologServiceProvider());
if ($app['debug']) {
    $app['monolog.logfile'] = __DIR__ . '/../logs/debug.log';
    $app['monolog.level'] = Monolog\Logger::DEBUG;
} else {
    $app['monolog.logfile'] = __DIR__ . '/../logs/production.log';
    $app['monolog.level'] = Monolog\Logger::WARNING;
}

$app->register(new Silex\Provider\SessionServiceProvider());

$app->register(new Silex\Provider\TranslationServiceProvider(array('locale' => $app['user_config']['locale'], 'locale_fallbacks' => array('en'))));

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
    	'driver'    => $app['user_config']['database']['driver'],
        'host'      => $app['user_config']['database']['host'],
        'dbname'    => $app['user_config']['database']['name'],
        'user'      => $app['user_config']['database']['user'],
        'password'  => $app['user_config']['database']['password'],
        'port'      => $app['user_config']['database']['port'],
        'charset'   => 'utf8',
    ),
));

$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => array(
        __DIR__ . '/../templates/',
        ),
    'twig.options' => array('strict_variables' => true, 'debug' => $app['debug']),
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->register(new Silex\Provider\ServiceControllerServiceProvider());

$app->register(new CCV\Provider\DoctrineORMServiceProvider(), array(
    'db.orm.proxies_dir'           => $app['cache_path'] . '/doctrine',
    'db.orm.auto_generate_proxies' => true,

    'db.orm.entities'              => array(array(
        'type'      => 'annotation',
        'path'      => __DIR__.'/CCV/Entity',
        'namespace' => 'CCV\Entity',
    )),
));

$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\ValidatorServiceProvider());

/**
 * Extending service providers
 * ========================================================================
 */
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    $twig->addExtension(new CCV\Twig\CCVTwigExtension());

    $twig->addGlobal('site_name', $app['user_config']['site_name']);
    $twig->addGlobal('exe_name', $app['user_config']['executable_name']);
    $twig->addGlobal('url', $app['url_generator']->generate('index', array(), Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));
    $twig->addGlobal('config', $app['user_config']);

    return $twig;
}));

/**
 * Set up translation
 * ========================================================================
 */
$messages = array();
$finder = new Symfony\Component\Finder\Finder();
$finder->files()->depth('== 0')->in($app['config_path'] . '/locales');
foreach ($finder as $file) {
    $messages[$file->getBasename('.yml')] = cache('locales/' . $file->getRelativePathname(), 'CCV\Configuration\TranslationLoader');
}

$app['translator.domains'] = array(
    'messages' => $messages
);

/**
 * Adding own variables
 * ========================================================================
 */
$app['script_twig'] = $app->share(function() use ($app) {
    $loader = new Twig_Loader_String();
    $twig = new Twig_Environment($loader);

    $twig->addGlobal('site_name', $app['user_config']['site_name']);
    $twig->addGlobal('exe_name', $app['user_config']['executable_name']);
    $twig->addGlobal('url', $app['url_generator']->generate('index', array(), Symfony\Component\Routing\Generator\UrlGeneratorInterface::ABSOLUTE_URL));

    return $twig;
});

$app['bank.controller'] = $app->share(function() use ($app) {
    return new CCV\Controller\BankController($app);
});

$app['ticket.controller'] = $app->share(function() use ($app) {
    return new CCV\Controller\TicketController($app);
});

$app['script.controller'] = $app->share(function() use ($app) {
    return new CCV\Controller\ScriptController($app);
});

$app['main.controller'] = $app->share(function() use ($app) {
    return new CCV\Controller\MainController($app);
});

$app->before(function () use ($app) {
    $app['locale'] = $app['user_config']['locale'];
});


/**
 * Controllers
 * ========================================================================
 */
$app->get('/', 'main.controller:indexAction')->bind('index');

$app->get('/db', function() use ($app) {
    $tool = new \Doctrine\ORM\Tools\SchemaTool($app['db.orm.em']);
    $classes = array(
      $app['db.orm.em']->getClassMetadata('CCV\Entity\Script'),
      $app['db.orm.em']->getClassMetadata('CCV\Entity\Ticket'),
      $app['db.orm.em']->getClassMetadata('CCV\Entity\TicketType'),
      $app['db.orm.em']->getClassMetadata('CCV\Entity\User')
    );
    return implode($tool->getCreateSchemaSql($classes), ";\n");
});

$app->get('/config', function() use ($app) {
    return Symfony\Component\Yaml\Yaml::dump($app['user_config'], 4);
});

$app->match('/scripts/main', 'main.controller:mainScriptAction')->bind('scripts.main');
$app->match('/scripts/install', 'main.controller:installScriptAction')->bind('scripts.install');

if ($app['user_config']['features']['bank']) {
    $app->match('/scripts/bank', 'main.controller:bankScriptAction')->bind('scripts.bank');
    $app->get('/bank/content', 'bank.controller:contentAction')->bind('bank.content');
    $app->get('/bank', 'bank.controller:indexAction')->bind('bank');
    $app->post('/bank/pay', 'bank.controller:payAction')->bind('bank.pay');
    $app->post('/bank/balance', 'bank.controller:balanceAction')->bind('bank.balance');
    $app->post('/bank/allbalance', 'bank.controller:allBalanceAction')->bind('bank.allbalance');
    $app->post('/bank/daily', 'bank.controller:dailyAction')->bind('bank.daily');
    $app->post('/bank/check', 'bank.controller:checkAction')->bind('bank.check');

    if ($app['user_config']['features']['tickets']) {
        $app->match('/scripts/ticket', 'main.controller:ticketScriptAction')->bind('scripts.ticket');
        $app->post('/tickets/create', 'ticket.controller:createAction')->bind('tickets.create');
        $app->post('/tickets/use', 'ticket.controller:useAction')->bind('tickets.use');
        $app->post('/tickets/check', 'ticket.controller:checkAction')->bind('tickets.check');
        $app->post('/tickets/type/price', 'ticket.controller:priceAction')->bind('tickets.type.price');
        $app->post('/tickets/type/add', 'ticket.controller:addTypeAction')->bind('tickets.type.add');
    }
}

$app->match('/scripts/add', 'script.controller:addAction')->bind('scripts.add');
$app->post('/scripts/put', 'script.controller:putAction')->bind('scripts.put');
$app->post('/scripts/list', 'script.controller:listAction')->bind('scripts.list');
$app->post('/scripts/raw', 'script.controller:rawAction')->bind('scripts.raw');
$app->match('/scripts/change/{id}', 'script.controller:changeAction')->bind('scripts.change')->assert('id', '\d+');
$app->get('/scripts/{slug}/{id}', 'script.controller:showAction')->bind('scripts.show')->assert('id', '\d+');


/**
 * Returning app
 * ========================================================================
 */
return $app;
