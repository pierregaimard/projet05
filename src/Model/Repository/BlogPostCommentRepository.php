<?php

namespace App\Model\Repository;

use App\Model\Entity\BlogPostComment;
use App\Model\Entity\BlogPostCommentStatus;
use Climb\Orm\EntityRepository;
use Climb\Exception\AppException;

class BlogPostCommentRepository extends EntityRepository
{
    /**
     * @param int $postId
     *
     * @return BlogPostComment[]|null
     *
     * @throws AppException
     */
    public function findByPost(int $postId)
    {
        return $this->findBy(
            [
                'id_blog_post' => $postId
            ],
            [EntityRepository::OPT_ORDER_BY => ['time' => 'DESC']]
        );
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
     * @param int $statusId
     *
     * @return BlogPostComment[]|null
     *
     * @throws AppException
     */
    public function findByStatus(int $statusId)
    {
        return $this->findBy(
            ['id_status' => $statusId],
            [EntityRepository::OPT_ORDER_BY => ['time' => 'DESC']]
        );
    }

    /**
     * @param int $statusId
     * @param int $postId
     *
     * @return BlogPostComment[]|null
     *
     * @throws AppException
     */
    public function findByPostAndStatus(int $postId, int $statusId)
    {
        return $this->findBy(
            [
                'id_blog_post' => $postId,
                'id_status' => $statusId
            ],
            [EntityRepository::OPT_ORDER_BY => ['time' => 'DESC']]
        );
    }
}
