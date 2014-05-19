<?php
/**
 *
 * @author Yuriy Berest <djua.com@gmail.com>
 */

class sfTwigLoaderNamespace extends sfTwigLoaderDirect
{

    /**
     * @var sfApplicationConfiguration
     */
    private $configuration;

    public function __construct(sfApplicationConfiguration $configuration)
    {
        $this->configuration = $configuration;
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
            throw new Twig_Error_Loader(sprintf(
                'Malformed template name "%s" (expecting "@namespace/template_name").',
                $name
            ));
        }

        $namespace = $m[1];
        $tplPath =ltrim($m[2], '/');
    }

    protected function findTemplate($path)
    {
        $path = $this->normalizeName($path);
        $this->parseName($path, $namespace, $tplPath);

        if ($namespace === 'root') {
            $fullFileName = sprintf('%s/%s', sfConfig::get('sf_app_template_dir'), $tplPath);
            $error = !is_file($fullFileName);
        } else {
            $fullFileName = $this->configuration->getTemplatePath($namespace, $tplPath);
            $error = $fullFileName === null;
        }

        if ($error) {
            throw new Twig_Error_Loader(sprintf(
                'Unable to find template "%s" (full file name "%s")',
                $path,
                $fullFileName
            ));
        }

        return $fullFileName;
    }
}
