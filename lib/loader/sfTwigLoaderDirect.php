<?php
/**
 *
 * @author Yuriy Berest <djua.com@gmail.com>
 */

class sfTwigLoaderDirect implements Twig_LoaderInterface
{

    /**
     * @var string
     */
    private $rootDir;

    /**
     * @param string $rootDir
     */
    public function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
    }

    private function isTplPathDeeperThanRoot($tplPath)
    {
        return strpos($tplPath, $this->rootDir) === 0;
    }

    protected function findTemplate($path)
    {
        $path = realpath($path);

        if ($path === false) {
            throw new Twig_Error_Loader(sprintf('Unable to find template file "%s"', $path));
        } elseif (!$this->isTplPathDeeperThanRoot($path)) {
            throw new Twig_Error_Loader(sprintf(
                'Looks like you try to load a template outside configured directories (%s).',
                $path
            ));
        }

        return $path;
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
}
