<?php

declare(strict_types=1);

namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Service;

use Concrete\Core\Page\Stack\Folder\FolderService;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Folder\Folder;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Page\Stack\Stack;

trait StackTrait
{
    /**
     * @var \Concrete\Core\Page\Stack\Folder\FolderService|null
     */
    private $folderService;

    /**
     * @var \Concrete\Core\Page\Page|null
     */
    private $foldersRootPage;

    /**
     * @var array|null
     */
    private $existingFolders;

    protected function getFolderService(): FolderService
    {
        if ($this->folderService === null) {
            $this->folderService = app(FolderService::class);
        }
        return $this->folderService;
    }

    protected function getFoldersRootPage(): Page
    {
        if ($this->foldersRootPage === null) {
            $this->foldersRootPage = Page::getByPath(STACKS_PAGE_PATH);
        }

        return $this->foldersRootPage;
    }

    /**
     * Get an existing folder.
     *
     * @return \Concrete\Core\Page\Stack\Folder\Folder|null returns NULL if $path is the root path (that is, '/' or ''), or if the folder doesn't exist
     */
    protected function getFolderByPath(string $path): ?Folder
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            return null;
        }
        $existingFolders = $this->getExistingFolders();

        return $existingFolders[$path] ?? null;
    }

    protected function getStackIDByName(string $name, ?Folder $folder = null): ?int
    {
        if ($folder !== null) {
            $parentPage = $folder->getPage();
        } else {
            $parentPage = $this->getFoldersRootPage();
        }
        $cn = app(Connection::class);
        $cID = $cn->fetchOne(
            'SELECT Stacks.cID FROM Stacks INNER JOIN Pages ON Stacks.cID = Pages.cID WHERE Stacks.stType <> :globalAreaType AND Stacks.stName = :name AND Pages.cParentID = :parentID',
            [
                'globalAreaType' => Stack::ST_TYPE_GLOBAL_AREA,
                'name' => $name,
                'parentID' => $parentPage->getCollectionID(),
            ]
        );

        return $cID ? (int) $cID : null;
    }
    

    /**
     * @return \Concrete\Core\Page\Stack\Folder\Folder|NULL returns NULL if $path is the root path (that is, '/' or '')
     */
    protected function getOrCreateFolderByPath(string $path): ?Folder
    {
        $path = '/' . trim($path, '/');
        if ($path === '/') {
            return null;
        }
        $existingFolders = $this->getExistingFolders();
        $tryPath = $path;
        $folder = null;
        while (true) {
            if (isset($existingFolders[$tryPath])) {
                $folder = $existingFolders[$tryPath];
                break;
            }
            $p = strrpos($tryPath, '/');
            if ($p === 0) {
                break;
            }
            $tryPath = substr($tryPath, 0, $p);
        }
        if ($folder === null) {
            $existingPath = '';
            $remainingPath = $path;
        } else {
            $existingPath = $tryPath;
            $remainingPath = substr($path, strlen($tryPath));
        }
        $remainingChunks = preg_split('{/}', $remainingPath, -1, PREG_SPLIT_NO_EMPTY);
        while ($remainingChunks !== []) {
            $name = array_shift($remainingChunks);
            $existingPath .= '/' . $name;
            $folder = $this->createFolder($name, $existingPath, $folder);
        }
        return $folder;
    }

    protected function createFolder(string $name, string $calculatedPath, ?Folder $parentFolder = null): Folder
    {
        $folder = $this->getFolderService()->add($name, $parentFolder);
        if ($this->existingFolders !== null) {
            $this->existingFolders[$calculatedPath] = $folder;
        }

        return $folder;
    }

    /**
     * @return \Concrete\Core\Page\Stack\Folder\Folder[] array keys are their path
     */
    private function getExistingFolders() : array
    {
        if ($this->existingFolders === null) {
            $this->existingFolders = [];
            $walk = null;
            $walk = function (Page $parentPage, $parentPagePath) use (&$walk) {
                foreach ($parentPage->getCollectionChildrenArray(true) as $childID) {
                    $childFolder = $this->getFolderService()->getByID($childID);
                    if (!$childFolder) {
                        continue;
                    }
                    $childFolderPage = $childFolder->getPage();
                    $childPath = $parentPagePath . '/' . $childFolderPage->getCollectionName();
                    $this->existingFolders[$childPath] = $childFolder;
                    $walk($childFolderPage, $childPath);
                }
            };
            $walk($this->getFoldersRootPage(), '');
        }
        
        return $this->existingFolders;
    }
}
