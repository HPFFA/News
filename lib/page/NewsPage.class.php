<?php
// Important for all Sites
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/data/News.class.php');

/**
 * Description of NewsPage
 *
 * @author logge002
 */


class NewsPage extends AbstractPage {
    // this is the part for the Template
    public $templateName = 'news';

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		// assign page parameters
		WCF::getTPL()->assign(array(
			'contentDisplay' => 'newsItem',
			'contentArray' => News::All()
		));
	}

    /**
     * @see Page::show()
     */
    public function show() {

        parent::show();
    }
}
?>
