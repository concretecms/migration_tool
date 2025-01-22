<?php
namespace PortlandLabs\Concrete5\MigrationTool\Publisher\Validator;

use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Stack\Stack;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\GlobalArea;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\StackFolder;
use PortlandLabs\Concrete5\MigrationTool\Publisher\Service\StackTrait;

/**
 * @property \PortlandLabs\Concrete5\MigrationTool\Entity\Import\AbstractStack $object
 */
class StackValidator extends AbstractValidator
{
    use StackTrait;

    /**
     * {@inheritdoc}
     *
     * @see \PortlandLabs\Concrete5\MigrationTool\Publisher\Validator\ValidatorInterface::skipItem()
     */
    public function skipItem()
    {
        $name = (string) $this->object->getName();
        if ($name === '') {
            return true;
        }
        if ($this->object instanceof StackFolder) {
            return $this->getFolderByPath(trim($this->object->getPath() ?? '', '/') . '/' . $name) !== null;
        }
        $batch = $this->getBatch($this->object);
        $site = $batch ? $batch->getSite() : null;
        if ($this->object instanceof GlobalArea) {
            $c = Stack::getByName($name, 'RECENT', $site);
        } else {
            $folder = $this->getFolderByPath($this->object->getPath());
            $cID = $this->getStackIDByName($name, $folder);
            $c = $cID === null ? null : Stack::getByID($cID);
        }
        if (!$c || $c->isError()) {
            return false;
        }
        $localeID = $this->object->getLocaleID();
        if ($localeID) {
            $localizedStack = null;
            
            if ($site) {
                $section = Section::getByLocale($localeID, $site);
                if ($section) {
                    $localizedStack = $c->getLocalizedStack($section);
                }
            }
            if (!$localizedStack) {
                return false;
            }
            $c = $localizedStack;
        }

        $blocks = $c->getBlocks();
        if (count($blocks)) {
            return true;
        }

        return false;
    }
}
