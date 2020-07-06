<?php

namespace GeminiLabs\SiteReviews\Modules\Html\Partials;

use GeminiLabs\SiteReviews\Database\ReviewManager;
use GeminiLabs\SiteReviews\Defaults\SiteReviewsDefaults;
use GeminiLabs\SiteReviews\Helper;
use GeminiLabs\SiteReviews\Helpers\Arr;
use GeminiLabs\SiteReviews\Modules\Html\ReviewHtml;
use GeminiLabs\SiteReviews\Modules\Html\ReviewsHtml;
use GeminiLabs\SiteReviews\Modules\Schema;
use GeminiLabs\SiteReviews\Review;
use GeminiLabs\SiteReviews\Reviews;

class SiteReviews
{
    /**
     * @var array
     */
    public $args;

    /**
     * @param Reviews|null $reviews
     * @return ReviewsHtml
     */
    public function build(array $args = [], $reviews = null)
    {
        $this->args = glsr(SiteReviewsDefaults::class)->merge($args);
        if (!$reviews instanceof Reviews) {
            $reviews = glsr(ReviewManager::class)->reviews($this->args);
        }
        $this->generateSchema($reviews);
        return $this->buildReviews($reviews);
    }

    /**
     * @return ReviewHtml
     */
    public function buildReview(Review $review)
    {
        glsr()->action('review/build/before', $review);
        $templateTags = [];
        foreach ($review as $key => $value) {
            $tag = $this->normalizeTemplateTag($key);
            $templateTags[$tag] = $this->buildTemplateTag($review, $tag, $value);
        }
        $templateTags = glsr()->filterArray('review/build/after', $templateTags, $review, $this);
        return new ReviewHtml($review, $templateTags);
    }

    /**
     * @return ReviewsHtml
     */
    public function buildReviews(Reviews $reviews)
    {
        $renderedReviews = [];
        foreach ($reviews as $index => $review) {
            $renderedReviews[] = $this->buildReview($review);
        }
        return new ReviewsHtml($renderedReviews, $reviews->max_num_pages, $this->args);
    }

    /**
     * @return void
     */
    public function generateSchema(Reviews $reviews)
    {
        if (wp_validate_boolean($this->args['schema']) && !wp_validate_boolean($this->args['is_block_editor'])) {
            glsr(Schema::class)->store(
                glsr(Schema::class)->build($this->args, $reviews)
            );
        }
    }

    /**
     * @param string $tag
     * @param string $value
     * @return string
     */
    protected function buildTemplateTag(Review $review, $tag, $value)
    {
        $args = $this->args;
        $classname = implode('-', ['review', $tag, 'tag']);
        $className = Helper::buildClassName($classname, 'Modules\Html\Tags');
        $field = class_exists($className)
            ? glsr($className, compact('tag', 'args'))->handleFor('review', $value, $review)
            : null;
        return glsr()->filterString('review/build/'.$tag, $field, $value, $review, $this);
    }

    /**
     * @param string $tag
     * @return string
     */
    protected function normalizeTemplateTag($tag)
    {
        $mappedTags = [
            'assigned_post_ids' => 'assigned_to',
        ];
        return Arr::get($mappedTags, $tag, $tag);
    }
}
