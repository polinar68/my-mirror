<?php

namespace GeminiLabs\SiteReviews\Modules\Html;

use ArrayObject;
use GeminiLabs\SiteReviews\Database\OptionManager;
use GeminiLabs\SiteReviews\Modules\Html\Partial;
use GeminiLabs\SiteReviews\Modules\Html\Template;
use GeminiLabs\SiteReviews\Reviews;

class ReviewsHtml extends ArrayObject
{
	/**
	 * @var array
	 */
	public $args;

	/**
	 * @var string
	 */
	public $navigation;

	/**
	 * @var int
	 */
	public $pages;

	/**
	 * @var array
	 */
	public $reviews;

	public function __construct( array $reviews, $maxPageCount, array $args )
	{
		$this->args = $args;
		$this->pages = $maxPageCount;
		$this->reviews = $reviews;
		$this->navigation = $this->buildPagination();
		parent::__construct( $reviews, ArrayObject::STD_PROP_LIST|ArrayObject::ARRAY_AS_PROPS );
	}

	/**
	 * @return string
	 */
	public function __get( $key )
	{
		return array_key_exists( $key, $this->reviews )
			? $this->reviews[$key]
			: '';
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return glsr( Template::class )->build( 'templates/reviews', [
			'args' => $this->args,
			'context' => [
				'assigned_to' => $this->args['assigned_to'],
				'category' => $this->args['category'],
				'class' => $this->getClass(),
				'id' => $this->args['id'],
				'navigation' => $this->getNavigation(),
				'reviews' => $this->getReviews(),
			],
		]);
	}

	/**
	 * @return string
	 */
	protected function buildPagination()
	{
		$pagination = glsr( Partial::class )->build( 'pagination', [
			'baseUrl' => glsr_get( $this->args, 'pagedUrl' ),
			'current' => glsr_get( $this->args, 'paged' ),
			'total' => $this->pages,
		]);
		$json = sprintf( '<glsr-pagination hidden data-atts=\'%s\'></glsr-pagination>', $this->args['json'] );
		return $pagination.$json;
	}

	/**
	 * @return string
	 */
	protected function getClass()
	{
		$defaults = [
			'glsr-reviews', 'glsr-default',
		];
		if( $this->args['pagination'] == 'ajax' ) {
			$defaults[] = 'glsr-ajax-pagination';
		}
		$classes = explode( ' ', $this->args['class'] );
		$classes = array_unique( array_merge( $defaults, $classes ));
		return implode( ' ', $classes );
	}

	/**
	 * @return string
	 */
	protected function getNavigation()
	{
		return wp_validate_boolean( $this->args['pagination'] )
			? $this->navigation
			: '';
	}

	/**
	 * @return string
	 */
	protected function getReviews()
	{
		return empty( $this->reviews )
			? $this->getReviewsFallback()
			: implode( PHP_EOL, $this->reviews );
	}

	/**
	 * @return string
	 */
	protected function getReviewsFallback()
	{
		if( empty( $this->args['fallback'] ) && glsr( OptionManager::class )->getBool( 'settings.reviews.fallback' )) {
			$this->args['fallback'] = __( 'There are no reviews yet. Be the first one to write one.', 'site-reviews' );
		}
		$fallback = '<p class="glsr-no-margins">'.$this->args['fallback'].'</p>';
		return apply_filters( 'site-reviews/reviews/fallback', $fallback, $this->args );
	}
}
