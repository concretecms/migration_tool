<?php
namespace PortlandLabs\Concrete5\MigrationTool\Importer\Sanitizer;

defined('C5_EXECUTE') or die("Access Denied.");

class PagePathSanitizer
{
    /**
     * @param string|\SimpleXMLElement|null $path
     *
     * @return string
     */
    public function sanitize($path)
    {
        $parts = preg_split('{/}', (string) $path, -1, PREG_SPLIT_NO_EMPTY);
        if ($parts === []) {
            return '';
        }
        $txt = \Core::make('helper/text');
        $pagePathSeparator = \Config::get('concrete.seo.page_path_separator');
        $parts = array_map(
            static function ($part) use ($txt, $pagePathSeparator) {
                return str_replace('-', $pagePathSeparator, $txt->slugSafeString($part));
            },
            $parts
        );

        return implode('/', $parts);
    }
}
