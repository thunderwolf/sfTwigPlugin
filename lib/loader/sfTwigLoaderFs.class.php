<?php

/**
 * Лоадер шаблонов приложения
 *
 * @author Yuriy Berest <djua.com@gmail.com>
 */
class sfTwigLoaderFs implements Twig_LoaderInterface
{

    protected $cache = array();

    /**
     * @var string
     */
    protected $tplRootDir;

    /**
     * @param string $tplRootDir
     */
    public function __construct($tplRootDir)
    {
        $this->tplRootDir = $tplRootDir;
    }
    /**
     * @param string $name
     *
     * @return string
     */
    protected function normalizeName($name)
    {
        $name = (string) $name;

        return preg_replace('#/{2,}#', '/', strtr($name, '\\', '/'));
    }

    protected function parseName($name, &$namespace, &$tplPath)
    {
        if (false !== strpos($name, "\0")) {
            throw new Twig_Error_Loader('A template name cannot contain NUL bytes.');
        }

        if (preg_match('/^@([a-z\_\-]+)((?:\/[a-z\_\-\+0-9]+)+\.[a-z]+)$/i', $name, $m) === 0) {
            throw new Twig_Error_Loader(sprintf('Malformed template name "%s" (expecting "@namespace/template_name").', $name));
        }

        $namespace = $m[1];
        $tplPath =ltrim($m[2], '/');
    }

    /**
     * получение полного пути к файлу по имени шаблона
     *
     * @param string $name имя шаблона
     *
     * @return string
     * @throws Twig_Error_Loader
     */
    protected function findTemplate($name)
    {
        $name = $this->normalizeName($name);

        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $this->parseName($name, $namespace, $tplPath);

        if ($namespace === 'root') {
            $fullFileName = sprintf('%s/templates/%s', $this->tplRootDir, $tplPath);
        } else {
            $fullFileName = sprintf('%s/modules/%s/templates/%s', $this->tplRootDir, $namespace, $tplPath);
        }

        if (is_file($fullFileName)) {
            return $this->cache[$name] = $fullFileName;
        }

        throw new Twig_Error_Loader(sprintf('Unable to find template "%s" (full file name "%s")', $name, $fullFileName));
    }

    /**
     * {@inheritdoc}
     */
    public function getSource($name)
    {
        return file_get_contents($this->findTemplate($name));
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey($name)
    {
        return $this->findTemplate($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh($name, $time)
    {
        return filemtime($this->findTemplate($name)) <= $time;
    }

    /**
     * получение имени шаблона по полному пути к файлу
     *
     * @param string $filePath
     *
     * @return string
     */
    public static function getTplNameByFilePath($filePath)
    {
        $filePath = realpath($filePath);

        $rCount = 0;
        $str = str_replace(sfConfig::get('sf_app_module_dir'), '', $filePath, $rCount);
        if (false === ($moduleNamespace = $rCount === 1)) {
            $str = str_replace(sfConfig::get('sf_app_dir'), '', $filePath, $rCount);
        }

        $str = ltrim($str, '/\\');
        $data = explode('/', $str);

        if ($moduleNamespace) {
            unset($data[1]);
        } else {
            $data[0] = 'root';
        }
        $result = '@' . implode('/', $data);

        return $result;
    }
}
