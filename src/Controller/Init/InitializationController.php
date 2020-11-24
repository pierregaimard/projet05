<?php

namespace App\Controller\Init;

use App\Service\Init\InitializationManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Routing\Annotation\Route;

class InitializationController extends AbstractController
{
    /**
     * @var InitializationManager
     */
    private InitializationManager $initManager;

    /**
     * @param InitializationManager $initManager
     */
    public function __construct(InitializationManager $initManager)
    {
        $this->initManager = $initManager;
    }

    /**
     * @Route(path="/initialize", name="initialize")
     *
     * @throws AppException
     */
    public function initialize()
    {
        if ($this->initManager->hasProjectInit()) {
            return $this->redirectToRoute('home');
        }

        // Initialize blog post Database and fixtures
        $this->initManager->initializeDatabase();
        $this->initManager->initAdminUser();

        // Set automatically Admin session
        $this->initManager->setUser();

        $response = new RedirectResponse(
            $this->getRoutePath('account_home'),
            $this->initManager->getData()
        );
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<b>Blog has been successfully initialized!</b><br>' .
                    'Please set your admin account informations.'
            ]
        );

        return $response;
    }
}
