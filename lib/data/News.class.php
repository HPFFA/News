<?php
require_once(WCF_DIR.'lib/data/message/Message.class.php');

/**
 * Description of NewsMessage
 *
 * @author logge002
 */
class News extends Message {
	
	
    function __construct($newsID, $row = null) {
        if ($newsID !== null) {
            $sql = "SELECT	*
				FROM 	wcf" . WCF_N . "_news
				WHERE 	newsID = ".$newsID;
            $row = WCF::getDB()->getFirstRow($sql);
        }
        parent::__construct($row);
    }
	
	private static function AllIDs()
	{
		$ids = array();
		$sql = "SELECT newsID 
				FROM wcf".WCF_N."_news";
		$result = WCF::getDB()->sendQuery($sql);
		while ($id = WCF::getDB()->fetchArray($result)){
			$ids[] = $id['newsID'];
		}
		return $ids;
	}
	
	public static function All()
	{
		$ids = News::AllIDs();
		$news = array();
		foreach ($ids as $id){
			$news[] = new News($id, null);
		}
		return $news;
	}
}
?>
