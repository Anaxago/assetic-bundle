<?php

/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Symfony\Bundle\AsseticBundle\Factory\Resource;

use Assetic\Factory\Resource\ResourceInterface;
use Symfony\Component\Templating\Loader\LoaderInterface;

/**
 * A file resource.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class FileResource implements ResourceInterface
{
    protected $loader;
    protected $bundle;
    protected $baseDir;
    protected $path;
    protected $template;

    /**
     * Constructor.
     *
     * @param LoaderInterface $loader  The templating loader
     * @param string          $bundle  The current bundle name
     * @param string          $baseDir The directory
     * @param string          $path    The file path
     */
    public function __construct($loader, $bundle, $baseDir, $path)
    {
        $this->loader = $loader;
        $this->bundle = $bundle;
        $this->baseDir = $baseDir;
        $this->path = $path;
    }

    public function isFresh($timestamp)
    {
        return false;
        //FIXME_HJA
        //$this->loader->isFresh($this->getTemplate(), $timestamp);
    }

    public function getContent()
    {
        $templateReference = $this->getTemplate();
        $tmpl = $templateReference;
        $tmpl = str_replace("Bundle", "", $tmpl);
        $tmpl = str_replace(":", "/", $tmpl);
        if(!preg_match("/^@/", $tmpl)) {
            $tmpl = "@" . $tmpl;
        }
        $fileResource = $this->loader->load($tmpl);

        if (!$fileResource) {
            throw new \InvalidArgumentException(sprintf('Unable to find template "%s".', $templateReference));
        }

        $o = $fileResource->unwrap()->getSourceContext()->getCode();
        return $o;
    }

    public function __toString()
    {
        return (string) $this->getTemplate();
    }

    protected function getTemplate()
    {
        if (null === $this->template) {
            //$this->template = self::createTemplateReference($this->bundle, substr($this->path, strlen($this->baseDir)));
            $this->template = '@' .  $this->bundle . "/" . substr($this->path, strlen($this->baseDir));
        }

        return $this->template;
    }

    private static function createTemplateReference($bundle, $file)
    {
        $parts = explode('/', strtr($file, '\\', '/'));
        $elements = explode('.', array_pop($parts));
        $engine = array_pop($elements);
        $format = array_pop($elements);
        $name = implode('.', $elements);

        return new TemplateReference($bundle, implode('/', $parts), $name, $format, $engine);
    }
}
