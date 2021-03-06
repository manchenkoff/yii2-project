<?php

declare(strict_types=1);

namespace app\core\components;

use yii\base\Component;
use yii\web\View;

/**
 * @property array $config
 * @property string $logo
 * @property string $name
 * @property string $title
 */
final class Seo extends Component
{
    private View $_view;

    private string $_name;

    private string $_logo;

    private array $meta = [];

    private array $links = [];

    /**
     * Initial loading when controller view was set
     */
    public function init(): void
    {
        // get current controller view
        $this->_view = app()->controller->view;

        // register all of SEO tags
        $this->registerData();

        // set new page title by magic-method
        $this->title = 'Undefined';
    }

    /**
     * Register all of the tags from configuration
     */
    private function registerData(): void
    {
        $this->links[] = [
            'rel' => 'canonical',
            'href' => url(request()->pathInfo, true),
        ];

        foreach ($this->links as $link) {
            $this->_view->registerLinkTag($link, $link['rel']);
        }

        foreach ($this->meta as $meta) {
            $this->_view->registerMetaTag($meta, $meta['name']);
        }
    }

    /**
     * Registers new page SEO data (for using in controllers)
     *
     * @param string $title
     * @param string $description
     * @param string $keywords
     * @param bool $useOpenGraph
     * @param string|null $image
     */
    public function registerPage(
        string $title,
        string $description,
        string $keywords,
        bool $useOpenGraph = false,
        string $image = null
    ): void {
        $this->title = $title;

        $this->meta[] = [
            'name' => 'description',
            'content' => $description,
        ];

        $this->meta[] = [
            'name' => 'keywords',
            'content' => $keywords,
        ];

        $this->registerData();

        if ($useOpenGraph) {
            $this->registerOpenGraph(
                $title,
                $description,
                $image
            );
        }
    }

    /**
     * Register OpenGraph main snippet card
     *
     * @param string|null $title
     * @param string|null $description
     * @param string|null $image
     */
    public function registerOpenGraph(
        string $title = null,
        string $description = null,
        string $image = null
    ): void {
        $params = [
            'og:url' => url(request()->url, true),
            'og:type' => 'article', // fixed
            'og:title' => $title ?? $this->title,
            'og:description' => $description ?? $this->get('description'),
            'og:image' => $image ?? $this->_logo,
            'og:site_name' => $this->_name,
        ];

        foreach ($params as $name => $content) {
            $this->_view->registerMetaTag(
                [
                    'name' => $name,
                    'content' => $content,
                ],
                $name
            );
        }
    }

    /**
     * Returns SEO item value by selected key
     *
     * @param string $key
     *
     * @return string|null
     */
    public function get(string $key): ?string
    {
        $value = null;

        // try to find in <meta> tags
        foreach ($this->meta as $meta) {
            if ($meta['name'] === $key) {
                $value = $meta['content'];
                break;
            }
        }

        // if not found yet
        if (is_null($value)) {
            // scan <link> tags
            foreach ($this->links as $link) {
                if ($link['rel'] === $key) {
                    $value = $link['href'];
                    break;
                }
            }
        }

        return $value;
    }

    /**
     * Magic method for loading configuration file (see config/main.php)
     *
     * @param array $config
     */
    protected function setConfig(array $config): void
    {
        $this->_name = $config['name'];
        $this->meta = $config['meta'];
        $this->links = $config['links'];
        $this->_logo = $config['logo'];
    }

    /**
     * Returns title of the current View
     * @return string
     */
    protected function getTitle(): string
    {
        return $this->_view->title;
    }

    /**
     * Sets new title with concatenating website name like: "Page | Website" (if exists)
     *
     * @param string $value
     */
    protected function setTitle(string $value): void
    {
        $this->_view->title = ($this->_name) ? "{$value} | {$this->_name}" : $value;
    }

    /**
     * Returns website name
     * @return string
     */
    protected function getName(): string
    {
        return $this->_name;
    }

    /**
     * Returns website logo
     * @return string
     */
    protected function getLogo(): string
    {
        return $this->_logo;
    }
}