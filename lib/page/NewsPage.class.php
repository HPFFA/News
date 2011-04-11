<?php
// Important for all Sites
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Description of NewsPage
 *
 * @author logge002
 */


class NewsPage extends AbstractPage {
    // this is the part for the Template
    public $templateName = 'news';


    /**
     * @see Page::show()
     */
    public function show() {

        parent::show();
    }
}
?>
