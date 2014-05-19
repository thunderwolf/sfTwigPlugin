<?php
/**
 * Класс для ленивой инициализации twig
 *
 * @author Yuriy Berest <djua.com@gmail.com>
 */
class sfTwigRenderEngine
{
    const SF_CONTEXT_ATTR_NAME = 'twig.engine';

    /**
     * @param sfContext $context
     *
     * @return sfTwigRenderEngine
     */
    public static function getInstanceFromContext(sfContext $context)
    {
        return $context->get(self::SF_CONTEXT_ATTR_NAME);
    }

    /**
     * @param sfContext $context
     */
    public static function addEngineToContext(sfContext $context)
    {
        if (!$context->has(self::SF_CONTEXT_ATTR_NAME)) {
            $context->set(self::SF_CONTEXT_ATTR_NAME, new sfTwigRenderEngine($context));
        }
    }

    /**
     * @var sfTwigEnvironment
     */
    protected $twig = null;

    /**
     * @var sfContext
     */
    protected $sfContext;

    /**
     * @param sfContext $context
     */
    public function __construct(sfContext $context)
    {
        $this->sfContext = $context;
    }

    /**
     * Создание и предварительное конфигурирование лоадера
     *
     * @return Twig_LoaderInterface
     */
    protected function createLoader()
    {
        return new Twig_Loader_Chain([
            new sfTwigLoaderDirect(sfConfig::get('sf_app_dir')),
            new sfTwigLoaderDirect(sfConfig::get('sf_plugins_dir')),
            new sfTwigLoaderNamespace($this->sfContext->getConfiguration())
        ]);
    }

    /**
     * @return sfTwigEnvironment
     */
    protected function createTwigInstance()
    {
        $twig = new sfTwigEnvironment($this->createLoader(), array(
            'cache' => sfConfig::get('sf_template_cache_dir'),
            'debug' => sfConfig::get('sf_debug', false),
            'sf_context' => $this->sfContext,
        ));

        if ($twig->isDebug()) {
            $twig->enableAutoReload();
            $twig->setCache(null);
        }

        return $twig;
    }

    /**
     * Загрузка необходимых расширений twig
     *
     * @return $this
     */
    protected function loadExtensions()
    {
        $extensions = sfConfig::get('sf_twig_extensions', array());

        foreach ($extensions as $extension) {
            $this->twig->addExtension(new $extension());
        }

        return $this;
    }

    /**
     * @return sfTwigEnvironment
     */
    public function getTwig()
    {
        if ($this->twig === null) {
            $this->twig = $this->createTwigInstance();
            $this->loadExtensions();
        }

        return $this->twig;
    }
}
