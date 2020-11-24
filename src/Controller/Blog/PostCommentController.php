<?php

namespace App\Controller\Blog;

use App\Model\Entity\BlogPost;
use App\Model\Entity\BlogPostComment;
use App\Model\Entity\BlogPostCommentStatus;
use App\Service\Comment\BlogPostCommentManager;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Http\Response;
use DateTime;

class PostCommentController extends AbstractController
{
    /**
     * @var FormTokenManager
     */
    private FormTokenManager $tokenManager;

    /**
     * @var EntityFormDataManager
     */
    private EntityFormDataManager $formManager;

    /**
     * @var BlogPostCommentManager
     */
    private BlogPostCommentManager $commentManager;

    /**
     * @param FormTokenManager       $tokenManager
     * @param EntityFormDataManager  $formManager
     * @param BlogPostCommentManager $commentManager
     */
    public function __construct(
        FormTokenManager $tokenManager,
        EntityFormDataManager $formManager,
        BlogPostCommentManager $commentManager
    ) {
        $this->tokenManager   = $tokenManager;
        $this->formManager    = $formManager;
        $this->commentManager = $commentManager;
    }

    /**
     * @Route(path="/blog/post/comment/add/{postKey}", name="post_comment_add", regex={"postKey"="[1-9]([0-9]*)"})
     * @Security(roles={"MEMBER", "ADMIN"})
     *
     * @param int $postKey
     *
     * @return Response
     *
     * @throws AppException
     */
    public function add(int $postKey)
    {
        $manager = $this->getOrm()->getManager('App');
        $post    = $manager->getRepository(BlogPost::class)->findOne($postKey);
        $data    = $this->getRequest()->getPost();

        // Check security token
        $tokenCheck = $this->tokenManager->isValid('BlogPostComment', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'blog_post_view',
                ['key' => $postKey],
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(BlogPostComment::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'blog_post_view',
                ['key' => $postKey],
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Set comment
        $status  = $manager->getRepository(BlogPostCommentStatus::class)->findOne(
            BlogPostCommentStatus::STATUS_VALIDATION
        );
        $comment = new BlogPostComment();
        $comment->setUser($this->getUser());
        $comment->setBlogPost($post);
        $comment->setStatus($status);
        $comment->setTime((new DateTime('NOW'))->format('Y-m-d H:m:s'));

        $data->remove('token');
        $this->formManager->setEntityFormData($comment, $data->getAll());

        // Insert comment
        $manager->insertOne($comment);

        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $postKey]));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> Your comment has been submitted.'
            ]
        );

        return $response;
    }

    /**
     * @Route(
     *     path="/blog/post/{postKey}/comment/delete/{key}",
     *     name="post_comment_delete",
     *     regex={"postKey"="[1-9]([0-9]*)", "key"="[1-9]([0-9]*)"}
     * )
     * @Security(roles={"MEMBER"})
     *
     * @param int $key
     * @param int $postKey
     *
     * @return Response
     *
     * @throws AppException
     */
    public function delete(int $postKey, int $key)
    {
        $manager  = $this->getOrm()->getManager('App');
        $comment  = $manager->getRepository(BlogPostComment::class)->findOne($key);
        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $postKey]));

        if (
            $comment->getUser()->getKey() !== $this->getUser()->getKey()
        ) {
            $response->getFlashes()->add(
                'message',
                [
                    'status' => 'danger',
                    'message' => "you can't delete this comment, it's not yours!"
                ]
            );

            return $response;
        }

        // Delete comment
        $manager->deleteOne($comment);

        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => "Your comment has been removed"
            ]
        );

        return $response;
    }

    /**
     * @Route(
     *     path="/blog/post/{postKey}/comment/approve/{key}",
     *     name="post_comment_validate",
     *     regex={"postKey"="[1-9]([0-9]*)", "key"="[1-9]([0-9]*)"}
     * )
     * @Security(roles={"ADMIN"})
     *
     * @param int $key
     * @param int $postKey
     *
     * @return Response
     *
     * @throws AppException
     */
    public function validate(int $postKey, int $key)
    {
        $this->commentManager->validate($key);

        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $postKey]));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => "The comment has been approved"
            ]
        );

        return $response;
    }

    /**
     * @Route(
     *     path="/blog/post/{postKey}/comment/reject/{key}",
     *     name="post_comment_reject",
     *     regex={"postKey"="[1-9]([0-9]*)", "key"="[1-9]([0-9]*)"}
     * )
     * @Security(roles={"ADMIN"})
     *
     * @param int $key
     * @param int $postKey
     *
     * @return Response
     *
     * @throws AppException
     */
    public function reject(int $postKey, int $key)
    {
        $this->commentManager->reject($key);

        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $postKey]));
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
