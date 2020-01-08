Skip to content
Search or jump to…

Pull requests
Issues
Marketplace
Explore

@hjanuschka
sanpii
/
assetic-bundle
forked from symfony/assetic-bundle
4
10230
 Code Issues 2 Pull requests 0 Actions Security Insights
assetic-bundle/Twig/AsseticNode.php
@StudioMaX StudioMaX Upgrade Twig from Underscored to Namespaces
72ce30a on 18 Sep 2019
@stof@kriswallsmith@jeremyFreeAgent@schmittjoh@kubawerlos@StudioMaX
114 lines (99 sloc)  3.37 KB

<?php
/*
 * This file is part of the Symfony framework.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */
namespace Symfony\Bundle\AsseticBundle\Twig;
use Assetic\Asset\AssetInterface;
use Assetic\Extension\Twig\AsseticNode as BaseAsseticNode;
use Twig\Compiler;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\GetAttrExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Template;
/**
 * Assetic node.
 *
 * @author Kris Wallsmith <kris@symfony.com>
 */
class AsseticNode extends BaseAsseticNode
{
    protected function compileAssetUrl(Compiler $compiler, AssetInterface $asset, $name)
    {
        $vars = array();
        foreach ($asset->getVars() as $var) {
            $vars[] = new ConstantExpression($var, $this->getTemplateLine());
            // Retrieves values of assetic vars from the context, $context['assetic']['vars'][$var].
            $vars[] = new GetAttrExpression(
                new GetAttrExpression(
                    new NameExpression('assetic', $this->getTemplateLine()),
                    new ConstantExpression('vars', $this->getTemplateLine()),
                    new ArrayExpression(array(), $this->getTemplateLine()),
                    Template::ARRAY_CALL,
                    $this->getTemplateLine()
                ),
                new ConstantExpression($var, $this->getTemplateLine()),
                new ArrayExpression(array(), $this->getTemplateLine()),
                Template::ARRAY_CALL,
                $this->getTemplateLine()
            );
        }
        $compiler
            ->raw('isset($context[\'assetic\'][\'use_controller\']) && $context[\'assetic\'][\'use_controller\'] ? ')
            ->subcompile($this->getPathFunction($name, $vars))
            ->raw(' : ')
            ->subcompile($this->getAssetFunction(new TargetPathNode($this, $asset, $name)))
        ;
    }
    private function getPathFunction($name, array $vars = array())
    {
        $nodes = array(new ConstantExpression('_assetic_'.$name, $this->getTemplateLine()));
        if (!empty($vars)) {
            $nodes[] = new ArrayExpression($vars, $this->getTemplateLine());
        }
        return new FunctionExpression(
            'path',
            new Node($nodes),
            $this->getTemplateLine()
        );
    }
    private function getAssetFunction($path)
    {
        $arguments = array($path);
        if ($this->hasAttribute('package')) {
            $arguments[] = new ConstantExpression($this->getAttribute('package'), $this->getTemplateLine());
        }
        return new FunctionExpression(
            'asset',
            new Node($arguments),
            $this->getTemplateLine()
        );
    }
}
class TargetPathNode extends AsseticNode
{
    private $node;
    private $asset;
    private $name;
    public function __construct(AsseticNode $node, AssetInterface $asset, $name)
    {
        $this->node = $node;
        $this->asset = $asset;
        $this->name = $name;
    }
    public function compile(Compiler $compiler)
    {
        BaseAsseticNode::compileAssetUrl($compiler, $this->asset, $this->name);
    }
    public function getLine()
    {
        return $this->node->getLine();
    }
}
© 2020 GitHub, Inc.
Terms
Privacy
Security
Status
Help
Contact GitHub
Pricing
API
Training
Blog
About
