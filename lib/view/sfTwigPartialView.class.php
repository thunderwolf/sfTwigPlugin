<?php

/*
 * This file is part of the sfTwigPlugin package.
 *
 * (c) Henrik Bjornskov <henrik@bearwoods.dk>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A partial view that uses Twig as the templating engine.
 *
 * @property sfEventDispatcher $dispatcher
 * @property sfParameterHolder $attributeHolder
 *
 * @package    sfTwigPlugin
 * @subpackage view
 * @author     Henrik Bjornskov <henrik@bearwoods.dk>
 * @author     Yurii Berest <djua.com@gmail.com>
 */
class sfTwigPartialView extends sfPartialView
{
    /**
     * @var string Extension used by twig templates. which is .html
     */
    protected $extension = '.twig';

    /**
     * Returns the Twig_Environment
     *
     * @return Twig_Environment
     */
    public function getEngine()
    {
        return sfTwigRenderEngine::getInstanceFromContext($this->context)->getTwig();
    }

    /**
     * This renders a file based on the $file and sf_type.
     *
     * @param string $file the fullpath to the template file
     *
     * @return string
     */
    protected function renderFile($file)
    {
        if (sfConfig::get('sf_logging_enabled', false)) {
            $this->dispatcher->notify(new sfEvent($this, 'application.log', array(sprintf('Render "%s"', $file))));
        }

        $this->loadCoreAndStandardHelpers();

        $event = $this->dispatcher->filter(
            new sfEvent($this, 'template.filter_parameters'),
            $this->attributeHolder->getAll()
        );

        return $this->getEngine()->loadTemplate($file)->render($event->getReturnValue());
    }
}
