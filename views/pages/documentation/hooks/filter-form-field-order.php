<div class="glsr-card postbox">
    <h3 class="glsr-card-heading">
        <button type="button" class="glsr-accordion-trigger" aria-expanded="false" aria-controls="hooks-filter-form-field-order">
            <span class="title">Customise the submission form field order</span>
            <span class="badge code">site-reviews/submission-form/order</span>
            <span class="icon"></span>
        </button>
    </h3>
    <div id="hooks-filter-form-field-order" class="inside">
        <p>Use this hook to customise the order of the fields in the review submission form used by Site Reviews.</p>
        <p>See the <code><a data-expand="#faq-change-form-field-order" href="<?= admin_url('edit.php?post_type='.glsr()->post_type.'&page=documentation#tab-faq'); ?>">FAQ</a></code> for a detailed example of how to use this hook.</p>
        <pre><code class="language-php">/**
 * Customises the order of the fields used in the Site Reviews submission form.
 * Paste this in your active theme's functions.php file.
 * @param array $order
 * @return array
 */
add_filter('site-reviews/submission-form/order', function ($order) {
    // modify the $order array here
    return $order;
});</code></pre>
    </div>
</div>