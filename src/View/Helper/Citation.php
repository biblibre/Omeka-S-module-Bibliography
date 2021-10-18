<?php declare(strict_types=1);
namespace Bibliography\View\Helper;

use Laminas\View\Helper\AbstractHelper;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;

class Citation extends AbstractHelper
{
    /**
     * Return a valid citation for this resource.
     *
     * @param AbstractResourceEntityRepresentation $resource
     * @param array $options Managed options are: "style", "locale", "defaults",
     * "bibliographic", "append_site", "append_date", "tag", and "template".
     * The default options are used for Omeka resources. You may unset options
     * to append the site and the access date for real bibliographic resources.
     * Other options are passed to template.
     * @return string
     */
    public function __invoke(AbstractResourceEntityRepresentation $resource, array $options = [])
    {
        $view = $this->getView();

        // Set default options.
        $options += [
            'style' => null,
            'locale' => null,
            'defaults' => [],
            'append_site' => true,
            'append_date' => true,
            'bibliographic' => false,
            'tag' => 'p',
        ];
        $options['resource'] = $resource;

        if ($options['bibliographic']) {
            $options['append_site'] = false;
            $options['append_date'] = false;
        }

        $currentSite = $this->currentSite();
        $currentSetting = $currentSite
            ? $view->plugin('siteSetting')
            : $view->plugin('setting');
        if (is_null($options['style'])) {
            $options['style'] = $currentSetting('bibliography_csl_style') ?: 'chicago-fullnote-bibliography';
        }
        if (is_null($options['locale'])) {
            $options['locale'] = $currentSetting('bibliography_csl_locale') ?: str_replace('_', '-', $currentSetting('locale'));
        } elseif (empty($options['locale'])) {
            $options['locale'] = 'en-US';
        }

        $template = empty($options['template']) ? 'common/citation' : $options['template'];
        unset($options['template']);

        return $view->partial($template, $options);
    }

    /**
     * Get the current site from the view.
     */
    protected function currentSite(): ?\Omeka\Api\Representation\SiteRepresentation
    {
        return $this->view->site ?? $this->view->site = $this->view
            ->getHelperPluginManager()
            ->get('Laminas\View\Helper\ViewModel')
            ->getRoot()
            ->getVariable('site');
    }
}
