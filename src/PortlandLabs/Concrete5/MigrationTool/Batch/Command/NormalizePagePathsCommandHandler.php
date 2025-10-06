<?php

namespace PortlandLabs\Concrete5\MigrationTool\Batch\Command;

use Doctrine\ORM\EntityManagerInterface;
use Generator;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\Batch;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\ImportedBlockValue;

class NormalizePagePathsCommandHandler
{
    public function __invoke(NormalizePagePathsCommand $command)
    {
        $em = app(EntityManagerInterface::class);
        $batch = $em->find(Batch::class, $command->getBatchId());
        $pages = $batch->getPages();
        $pagesToNormalize = [];
        foreach ($pages as $page) {
            if ($batch->isPublishToSitemap() || !$page->canNormalizePath()) {
                $page->setBatchPath($page->getOriginalPath());
            } else {
                $pagesToNormalize[] = $page;
            }
        }
        $map = $this->normalizePagePaths($pagesToNormalize);
        $this->applyMapToPageLinks($pages, $batch, $map);
        $em->flush();
    }

    /**
     * Set the "Batch Path" of the pages, and returns a map from the "original" page paths and the "batch" paths.
     *
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     */
    private function normalizePagePaths(array $pagesToNormalize): array
    {
        $map = [];
        $commonPrefix = $this->calculateCommonPathPrefix($pagesToNormalize);
        foreach ($pagesToNormalize as $page) {
            $originalPath = '/' . ltrim($page->getOriginalPath() ?? '', '/');
            $newPath = substr($originalPath, strlen($commonPrefix) - 1);
            $page->setBatchPath($newPath);
            $map[$originalPath] = $newPath;
        }

        return $map;
    }

    /**
     * Calculate the common prefix of page paths.
     *
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     *
     * @example for 0 pages: you'll get '/'
     * @example for 1 page at '/path/to/my/page': you'll get '/path/to/my/'
     * @example for 2 pages at '/path/to/my/page' and '/path/to/another/page': you'll get '/path/to/'
     * @example for 2 pages at '/first/page' and '/second/page': you'll get '/'
     */
    private function calculateCommonPathPrefix(array $pages): string
    {
        $commonSlugs = null;
        foreach ($pages as $page) {
            $pageSlugs = preg_split('{/}', $page->getOriginalPath() ?? '', -1, PREG_SPLIT_NO_EMPTY);
            array_pop($pageSlugs);
            if ($commonSlugs === null) {
                $commonSlugs = $pageSlugs;
            } else {
                $newCommonSlugs = [];
                foreach ($commonSlugs as $index => $slug) {
                    if (!isset($pageSlugs[$index]) || $pageSlugs[$index] !== $slug) {
                        break;
                    }
                    $newCommonSlugs[] = $slug;
                }
                $commonSlugs = $newCommonSlugs;
            }
            if ($commonSlugs === []) {
                break;
            }
        }
        if ($commonSlugs === null || $commonSlugs === []) {
            return '/';
        }

        return '/' . implode('/', $commonSlugs) . '/';
    }

    /**
     * Update the '{ccm:export:page:...}` placeholders of the blocks.
     *
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     * @param array $map the map from the "original" page paths and the "batch" paths.
     */
    private function applyMapToPageLinks(array $pages, Batch $batch, array $map): void
    {
        $pathPrefix = $batch->isPublishToSitemap() ? '' : "/!import_batches/{$batch->getID()}";
        foreach ($this->listImportedBlockValues($pages) as $importedBlockValue) {
            $value = $importedBlockValue->getOriginalValue();
            if ($value) {
                $value = preg_replace_callback(
                    '/\{ccm:export:page:(?<path>.*?)\}/',
                    static function (array $matches) use (&$map, $pathPrefix): string {
                        $path = '/' . ltrim($matches['path'], '/');
                        if (isset($map[$path])) {
                            $path = $pathPrefix . $map[$path];
                        }
                        if ($path === '/') {
                            $path = '';
                        }
                        return "{ccm:export:page:{$path}}";
                    },
                    $value
                );
            }
            $importedBlockValue->setValue($value);
        }
    }

    /**
     * List all the ImportedBlockValue instances owned by pages.
     *
     * @param \PortlandLabs\Concrete5\MigrationTool\Entity\Import\Page[] $pages
     *
     * @return \PortlandLabs\Concrete5\MigrationTool\Entity\Import\BlockValue\ImportedBlockValue[]
     */
    private function listImportedBlockValues(array $pages): Generator
    {
        foreach ($pages as $page) {
            foreach ($page->getAreas() as $area) {
                foreach ($area->getBlocks() as $block) {
                    $value = $block->getBlockValue();
                    if ($value instanceof ImportedBlockValue) {
                        yield $value;
                    }
                }
            }
        }
    }
}
