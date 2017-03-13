<?php
/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */

namespace Pimcore\Document\Area;

use Pimcore\Bundle\PimcoreBundle\HttpKernel\BundleLocator\BundleLocatorInterface;

/**
 * Auto-resolves view and edit templates if has*Template properties are set. Depending on the result of getTemplateLocation
 * and getTemplateSuffix it builds the following template references:
 *
 * - <currentBundle>:Areas/<brickId>/(view|edit).<suffix>
 * - Areas/<brickId>/(view|edit).<suffix> -> resolves to app/Resources
 */
abstract class AbstractTemplateAreabrick extends AbstractAreabrick
{
    const TEMPLATE_LOCATION_GLOBAL = 'global';
    const TEMPLATE_LOCATION_BUNDLE = 'bundle';

    const TEMPLATE_SUFFIX_PHP  = 'html.php';
    const TEMPLATE_SUFFIX_TWIG = 'html.twig';

    /**
     * @var bool
     */
    protected $hasViewTemplate = true;

    /**
     * @var bool
     */
    protected $hasEditTemplate = false;

    /**
     * @var BundleLocatorInterface
     */
    protected $bundleLocator;

    /**
     * @var array
     */
    protected $templateReferences = [];

    /**
     * @var string
     */
    protected $bundleName = null;

    /**
     * @param BundleLocatorInterface $bundleLocator
     */
    public function __construct(BundleLocatorInterface $bundleLocator)
    {
        $this->bundleLocator = $bundleLocator;
    }

    /**
     * Determines if template should be auto-located in area bundle or in app/Resources
     *
     * @return string
     */
    protected function getTemplateLocation()
    {
        return static::TEMPLATE_LOCATION_BUNDLE;
    }

    /**
     * Returns view suffix used to auto-build view names
     *
     * @return string
     */
    protected function getTemplateSuffix()
    {
        return static::TEMPLATE_SUFFIX_PHP;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewTemplate()
    {
        if (!$this->hasViewTemplate) {
            return null;
        }

        return $this->resolveTemplateReference('view');
    }

    /**
     * {@inheritdoc}
     */
    public function getEditTemplate()
    {
        if (!$this->hasEditTemplate) {
            return null;
        }

        return $this->resolveTemplateReference('edit');
    }

    /**
     * @param string $type
     * @return string
     */
    protected function resolveTemplateReference($type)
    {
        if (!isset($this->templateReferences[$type])) {
            $this->templateReferences[$type] = $this->getTemplateReference($type);
        }

        return $this->templateReferences[$type];
    }

    /**
     * @return string
     */
    protected function getBundleName()
    {
        if (null === $this->bundleName) {
            try {
                $this->bundleName = $this->bundleLocator->getBundle($this)->getName();
            } catch (\Exception $e) {
                $this->bundleName = "AppBundle";
            }
        }

        return $this->bundleName;
    }

    /**
     * Return either bundle or global (= app/Resources) template reference
     *
     * @param string $type
     * @return string
     */
    protected function getTemplateReference($type)
    {
        if ($this->getTemplateLocation() === static::TEMPLATE_LOCATION_BUNDLE && $this->getBundleName() != "AppBundle") {
            return sprintf(
                '%s:Areas/%s:%s.%s',
                $this->getBundleName(),
                $this->getId(),
                $type,
                $this->getTemplateSuffix()
            );
        } else {
            return sprintf(
                'Areas/%s/%s.%s',
                $this->getId(),
                $type,
                $this->getTemplateSuffix()
            );
        }
    }
}
