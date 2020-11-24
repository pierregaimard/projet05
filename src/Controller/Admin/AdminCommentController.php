<?php

namespace App\Controller\Admin;

use App\Model\Entity\BlogPostComment;
use App\Model\Entity\BlogPostCommentStatus;
use App\Service\Comment\BlogPostCommentManager;
use Climb\Controller\AbstractController;
use Climb\Http\RedirectResponse;
use Climb\Http\Response;
use Climb\Exception\AppException;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;

class AdminCommentController extends AbstractController
{
    /**
     * @var BlogPostCommentManager
     */
    private BlogPostCommentManager $commentManager;

    /**
     * @param BlogPostCommentManager $commentManager
     */
    public function __construct(BlogPostCommentManager $commentManager)
    {
        $this->commentManager = $commentManager;
    }

    /**
     * @Route(path="/admin/comment/validationList", name="admin_comment_validation_list")
     * @Security(roles={"ADMIN"})
     *
     * @return Response
     *
     * @throws AppException
     */
    public function list()
    {
        $manager  = $this->getOrm()->getManager('App');
        $comments = $manager->getRepository(BlogPostComment::class)->findByStatus(
            BlogPostCommentStatus::STATUS_VALIDATION
        );

        $response = new Response();
        $response->setContent($this->render(
            'admin/comment_validation/comments_validation_list.html.twig',
            [
                'comments' => $comments
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/admin/comment/validate/{key}", name="admin_comment_validation", regex={"key"="[1-9]([0-9]*)"})
     * @Security(roles={"ADMIN"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function validate(int $key)
    {
        $this->commentManager->validate($key);

        $response = new RedirectResponse($this->getRoutePath('admin_comment_validation_list'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => "The comment has been validated"
            ]
        );

        return $response;
    }

    /**
     * @Route(path="/admin/comment/reject/{key}", name="admin_comment_rejection", regex={"key"="[1-9]([0-9]*)"})
     * @Security(roles={"ADMIN"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function reject(int $key)
    {
        $this->commentManager->reject($key);

        $response = new RedirectResponse($this->getRoutePath('admin_comment_validation_list'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => "The comment has been rejected"
            ]
        );

        return $response;
    }
}
