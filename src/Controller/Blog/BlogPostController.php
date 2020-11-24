<?php

namespace App\Controller\Blog;

use App\Model\Entity\BlogPost;
use App\Model\Entity\BlogPostComment;
use App\Model\Entity\BlogPostCommentStatus;
use App\Model\Entity\User;
use App\Service\Form\EntityFormDataManager;
use App\Service\Security\FormTokenManager;
use Climb\Controller\AbstractController;
use Climb\Exception\AppException;
use Climb\Http\RedirectResponse;
use Climb\Http\Response;
use Climb\Orm\EntityRepository;
use Climb\Routing\Annotation\Route;
use Climb\Security\Annotation\Security;
use DateTime;

class BlogPostController extends AbstractController
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
     * @param FormTokenManager      $tokenManager
     * @param EntityFormDataManager $formManager
     */
    public function __construct(FormTokenManager $tokenManager, EntityFormDataManager $formManager)
    {
        $this->tokenManager = $tokenManager;
        $this->formManager  = $formManager;
    }

    /**
     * @Route(path="/blog", name="blog")
     *
     * @throws AppException
     */
    public function list()
    {
        $manager        = $this->getOrm()->getManager('App');
        $postRepository = $manager->getRepository(BlogPost::class);
        $posts          = $postRepository->findAll(
            [
                EntityRepository::OPT_ORDER_BY => ['creation_time' => 'DESC']
            ]
        );

        $response = new Response();
        $response->setContent($this->render(
            'blog/blog.html.twig',
            [
                'posts' => $posts
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/blog/addPost", name="blog_post_add")
     * @Security(roles={"ADMIN"})
     */
    public function add()
    {
        $token = $this->tokenManager->getToken('addBlogPost');
        $data  = $this->getRequestData();

        $response = new Response();
        $response->setContent($this->render(
            'blog/post/edit.html.twig',
            [
                'token' => $token,
                'message' => $data->get('message'),
                'formCheck' => $data->get('formCheck'),
                'formData' => $data->get('formData'),
                'action' => 'add'
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/blog/addPost/check", name="blog_post_add_check")
     * @Security(roles={"ADMIN"})
     *
     * @throws AppException
     */
    public function addCheck()
    {
        $data = $this->getRequest()->getPost();

        // Check security token
        $tokenCheck = $this->tokenManager->isValid('addBlogPost', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'blog_post_add',
                null,
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(BlogPost::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'blog_post_add',
                null,
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Set blog post
        $post = new BlogPost();
        $now  = (new DateTime('NOW'))->format('Y-m-d');
        $post->setUser($this->getUser());
        $post->setCreationTime($now);
        $post->setLastUpdateTime($now);
        $data->remove('token');
        $this->formManager->setEntityFormData($post, $data->getAll());

        // Insert blog post
        $manager = $this->getOrm()->getManager('App');
        $postId  = $manager->insertOne($post);

        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $postId]));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> Your new blog post is online!'
            ]
        );

        return $response;
    }

    /**
     * @Route(path="/blog/post/view/{key}", name="blog_post_view", regex={"key"="[1-9]([0-9]*)"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function view(int $key)
    {
        $token              = $this->tokenManager->getToken('BlogPostComment');
        $data               = $this->getRequestData();
        $manager            = $this->getOrm()->getManager('App');
        $post               = $manager->getRepository(BlogPost::class)->findOne($key);
        $commentsRepository = $manager->getRepository(BlogPostComment::class);
        $comments           = null;
        $user               = $this->getUser();

        // If visitor: approved comments
        if ($user === null) {
            $comments = $commentsRepository->findByPostAndStatus(
                $post->getKey(),
                BlogPostCommentStatus::STATUS_APPROVED
            );
        }
        // If member: approved comments and member own comments (approved or not)
        if ($user && $this->getUser()->isGranted(User::ROLE_MEMBER)) {
            $comments = $commentsRepository->findByPostAndMember($post->getKey(), $this->getUser()->getKey());
        }
        // If Admin: all comments (approved or not)
        if ($user && $this->getUser()->isGranted(User::ROLE_ADMIN)) {
            $comments = $commentsRepository->findByPost($post->getKey());
        }

        $response = new Response();
        $response->setContent($this->render(
            'blog/post/view.html.twig',
            [
                'post' => $post,
                'token' => $token,
                'message' => $data->get('message'),
                'formData' => $data->get('formData'),
                'formCheck' => $data->get('formCheck'),
                'comments' => $comments
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/blog/post/edit/{key}", name="blog_post_edit", regex={"key"="[1-9]([0-9]*)"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function edit(int $key)
    {
        $manager      = $this->getOrm()->getManager('App');
        $post         = $manager->getRepository(BlogPost::class)->findOne($key);
        $users        = $manager->getRepository(User::class)->findByRole('ADMIN');
        $usersOptions = [];

        foreach ($users as $user) {
            $usersOptions[] = [
                'value' => $user->getKey(),
                'label' => $user->getFormattedName('large')
            ];
        }

        $token    = $this->tokenManager->getToken('editBlogPost');
        $data     = $this->getRequestData();
        $formData = $data->get('formData');

        // Hydrate form fields if formData is empty
        if ($formData === false) {
            $formData = [
                'title' => $post->getTitle(),
                'chapo' => $post->getChapo(),
                'content' => $post->getHtmlContent()
            ];
        }

        $response = new Response();
        $response->setContent($this->render(
            'blog/post/edit.html.twig',
            [
                'token' => $token,
                'post' => $post,
                'usersOptions' => $usersOptions,
                'formData' => $formData,
                'formCheck' => $data->get('formCheck'),
                'action' => 'edit',
            ]
        ));

        return $response;
    }

    /**
     * @Route(path="/blog/editPost/check/{key}", name="blog_post_edit_check", regex={"key"="[1-9]([0-9]*)"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function editCheck(int $key)
    {
        $manager = $this->getOrm()->getManager('App');
        $post    = $manager->getRepository(BlogPost::class)->findOne($key);
        $data    = $this->getRequest()->getPost();

        // Check security token
        $tokenCheck = $this->tokenManager->isValid('editBlogPost', $data->get('token'));
        if ($tokenCheck !== true) {
            return $this->redirectToRoute(
                'blog_post_edit',
                ['key' => $key],
                ['message' => $tokenCheck, 'formData' => $data->getAll()]
            );
        }

        // Check form data
        $formCheck = $this->formManager->checkFormData(BlogPost::class, $data->getAll());
        if (is_array($formCheck)) {
            return $this->redirectToRoute(
                'blog_post_edit',
                ['key' => $key],
                ['formCheck' => $formCheck, 'formData' => $data->getAll()]
            );
        }

        // Update post
        $manager = $this->getOrm()->getManager('App');
        $user    = $manager->getRepository(User::class)->findOne($data->get('user'));
        $now     = (new DateTime('NOW'))->format('Y-m-d');
        $post->setLastUpdateTime($now);
        $data->remove('token');
        $data->remove('user');
        $this->formManager->setEntityFormData($post, $data->getAll());
        $post->setUser($user);
        $manager->updateOne($post);

        $response = new RedirectResponse($this->getRoutePath('blog_post_view', ['key' => $key]));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> Your blog post has been updated!'
            ]
        );

        return $response;
    }

    /**
     * @Route(path="/blog/post/delete/{key}", name="blog_post_delete", regex={"key"="[1-9]([0-9]*)"})
     *
     * @param int $key
     *
     * @return Response
     *
     * @throws AppException
     */
    public function delete(int $key)
    {
        $manager = $this->getOrm()->getManager('App');
        $post    = $manager->getRepository(BlogPost::class)->findOne($key);

        // Delete post
        $manager->deleteOne($post);

        $response = new RedirectResponse($this->getRoutePath('blog'));
        $response->getFlashes()->add(
            'message',
            [
                'status' => 'success',
                'message' => '<span uk-icon="check"></span> Blog post "' . $post->getTitle() . '" has been deleted'
            ]
        );

        return  $response;
    }
}
