<?php
namespace PortlandLabs\Concrete5\MigrationTool\Event;

use Concrete\Core\Application\Application;
use Concrete\Core\Application\Service\Dashboard;
use Concrete\Core\Command\Process\Event\ProcessEvent;
use Concrete\Core\Http\ResponseAssetGroup;
use Concrete\Core\Page\View\PageView;
use Doctrine\ORM\EntityManager;
use PortlandLabs\Concrete5\MigrationTool\Entity\Import\BatchProcess;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class EventSubscriber implements EventSubscriberInterface
{

    /**
     * @var Application
     */
    protected $app;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(Application $app, EntityManager $entityManager)
    {
        $this->app = $app;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return array(
            'on_close_process' => array(
                array('onCloseProcess')
            ),
            'on_before_render' => array(
                array('onBeforeRender')
            ),
        );
    }

    public function onBeforeRender(GenericEvent $ev)
    {
        $view = $ev->getArgument('view');
        if ($view && $view instanceof PageView) {
            $dashboard = $this->app->make(Dashboard::class);
            if ($dashboard->inDashboard($view->getPageObject())) {
                $responseAssetGroup = ResponseAssetGroup::get();
                $responseAssetGroup->requireAsset('migration_tool/backend');
            }
        }
    }

    public function onCloseProcess(ProcessEvent $ev)
    {
        // Let's look for any batch process objects joined to this process. If they exist let's delete them.

        $process = $ev->getProcess();
        if ($process) {
            $batchProcess = $this->entityManager->getRepository(BatchProcess::class)
                ->findOneByProcess($process);
            if ($batchProcess) {
                // Let's unlink the process object so it sticks around in the logs (and doesn't screw up polling by
                // not existing) and then remove the batch process object.
                $batchProcess->setProcess(null);
                $this->entityManager->persist($batchProcess);
                $this->entityManager->flush();

                $this->entityManager->remove($batchProcess);
                $this->entityManager->flush();
            }
        }
    }
    

}
