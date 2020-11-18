<?php

namespace App\Model\Repository;

use App\Model\Entity\BlogPostComment;
use Climb\Orm\EntityRepository;
use Climb\Exception\AppException;

class BlogPostCommentRepository extends EntityRepository
{
    /**
     * @param int $postId
     *
     * @return array|null
     *
     * @throws AppException
     */
    public function findByPost(int $postId)
    {
        $request = '
            SELECT * from blog_post_comment
            WHERE id_blog_post = :id_blog_post
            ORDER BY blog_post_comment.time DESC
        ';

        return $this->findByRequest($request, ['id_blog_post' => $postId]);
    }

    /**
     * @param int $postId
     * @param int $memberId
     *
     * @return array|null
     *
     * @throws AppException
     */
    public function findByPostAndMember(int $postId, int $memberId)
    {
        $request = '
            SELECT * from blog_post_comment
            WHERE (
                (id_user = :id_user AND id_status = 1)
                OR (id_status = 2)
            )
            AND id_blog_post = :id_blog_post
            ORDER BY blog_post_comment.time DESC
        ';

        return $this->findByRequest($request, ['id_user' => $memberId, 'id_blog_post' => $postId]);
    }

    /**
     * @param string $status
     * @param int    $postId
     *
     * @return BlogPostComment[]|null
     *
     * @throws AppException
     */
    public function findByPostAndStatus(int $postId, string $status)
    {
        $request = '
            SELECT * from blog_post_comment
            INNER JOIN blog_post_comment_status
                ON blog_post_comment_status.id = blog_post_comment.id_status
                AND blog_post_comment_status.status = :status
            WHERE id_blog_post = :id_blog_post
            ORDER BY blog_post_comment.time DESC
        ';

        return $this->findByRequest($request, ['status' => $status, 'id_blog_post' => $postId]);
    }
}
