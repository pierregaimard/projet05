<?php

namespace App\Controller;

use App\Model\Entity\Message;
use App\Service\Email\EmailManager;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use App\Service\Security\UserSecurityManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\Response;

class HomeController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formDataManager;

    /**
     * @var EmailManager
     */
    private EmailManager $emailManager;

    /**
     * @var UserSecurityManager
     */
    private UserSecurityManager $userManager;

    /**
     * @param FormTokenManager      $tokenManager
     * @param EntityFormDataManager $formDataManager
     * @param EmailManager          $emailManager
     * @param UserSecurityManager   $userManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formDataManager,
        EmailManager $emailManager,
        UserSecurityManager $userManager
    ) {
        $this->tokenManager    = $tokenManager;
        $this->formDataManager = $formDataManager;
        $this->emailManager    = $emailManager;
        $this->userManager     = $userManager;
    }

    /**
     * @return Response
     *
     * @Route(path="/", name="home")
     */
    public function home()
    {
        $token = $this->tokenManager->getToken('contactForm');

        $response = new Response();
        $response->setContent($this->render(
            'home/home.html.twig',
            [
                'token' => $token,
                'message' => $this->getRequestData()->get('message'),
                'formCheck' => $this->getRequestData()->get('formCheck'),
                'formData' => $this->getRequestData()->get('formData')
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/contactFormCheck", name="contact_form_check")
     *
     * @throws AppException
     */
    public function contactFormCheck()
    {
        $data = $this->getRequest()->getPost();

        // Check form token
        $tokenCheck = $this->tokenManager->isValid('contactForm', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute('home', ['step' => 'stepOne'], ['message' => $tokenCheck,]);
        }

        // Check form data
        $formCheck = $this->formDataManager->checkFormData(Message::class, $data->getAll());
        if ($formCheck !== true) {
            return $this->redirectToRoute(
                'home',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Filter formData
        $message = new Message();
        $data->remove('token');
        $this->formDataManager->setEntityFormData($message, $data->getAll());
        $admin = $this->userManager->getAdminUser();

        // Send message to admin
        $this->emailManager->send(
            $admin->getEmail(),
            'You have a message from your blog',
            'home/_contact_mail.html.twig',
            ['message' => $message]
        );

        return $this->redirectToRoute('home');
    }
}
