<?php

namespace Sng\AdditionalReports\ViewHelpers\Widget;

/*
 * This file is part of the "additional_reports" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

/**
 * This ViewHelper renders a Pagination of objects.
 * Inspired by class of "news" extension
 *
 * = Examples =
 *
 * <code title="required arguments">
 * <f:widget.paginate query="{query}" as="paginatedBlogs">
 *   // use {paginatedBlogs} as you used {blogs} before, most certainly inside
 *   // a <f:for> loop.
 * </f:widget.paginate>
 * </code>
 */
class PaginateQueryViewHelper extends \TYPO3\CMS\Fluid\Core\Widget\AbstractWidgetViewHelper
{

    /**
     * @var \Sng\AdditionalReports\ViewHelpers\Widget\Controller\PaginateQueryController
     */
    protected $controller;

    /**
     * Inject controller
     *
     * @param \Sng\AdditionalReports\ViewHelpers\Widget\Controller\PaginateQueryController $controller
     * @return void
     */
    public function injectController(\Sng\AdditionalReports\ViewHelpers\Widget\Controller\PaginateQueryController $controller)
    {
        $this->controller = $controller;
    }

    /**
     * Render everything
     *
     * @param array  $query
     * @param string $as
     * @param mixed  $configuration
     * @return string
     */
    public function render($query, $as, $configuration = array('itemsPerPage' => 10, 'insertAbove' => false, 'insertBelow' => true))
    {
        return $this->initiateSubRequest();
    }
}

?>