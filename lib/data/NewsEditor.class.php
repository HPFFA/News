<?php
require_once(WCF_DIR.'lib/data/News.class.php');

/**
 * Description of NewsEditor
 *
 * @author logge002
 */
class NewsEditor extends News {

        /**
         * creates a news item which is not in the database
         * @param <type> $title
         * @param <type> $summary
         * @param <type> $text
         * @param <type> $authorname
         * @param <type> $enableSmilies
         * @param <type> $enableHtml
         * @param <type> $enableBBCodes
         * @return News
         */
        static public function createPreview($title, $summary, $text, $authorname ,$enableSmilies = 1, $enableHtml = 0, $enableBBCodes= 1) {

            $news = new News(null, array(
                'newsID' => 0,
                'title' => $title,
                'summary' => $summary,
                'text' => $text,
                'authorname' => $authorname,
                'enableSmilies' => $enableSmilies,
                'enableHtml' => $enableHtml,
                'enableBBCodes' => $enableBBCodes));
            return $news;
        }

        /**
         * looks for a news item in the data base with the given content</br>
         * a item is identical only if title and text or title and summary equals
         * @param <string> $title
         * @param <string> $summary
         * @param <string> $text
         * @param <integer> $newsID default 0
         */
        static public function test($title, $summary, $text, $newsID = 0){
                //$hash = StringUtil::getHash(($newsID ? newsID : '') . $title . $summary . $text);
                //$sql = "SELECT  newsID
                //      FROM   wcf".WCF_N."_post_hash
                //      WHERE   messageHash = '".$hash."'";
                $sqlBase = "SELECT newsID
                        FROM wcf".WCF_N."_news
                        WHERE ";
                $sql = $sqlBase."title = '".$title."' and text = '".$text."'";
                $row = WCF::getDB()->getFirstRow($sql);
                if (!empty($row['newsID'])){
                        return $row['newsID'];
                }
                $sql = $sqlBase."title = '".$title."' and summary = '".$summary."'";
                $row = WCF::getDB()->getFirstRow($sql);
                if (!empty($row['newsID'])){
                        return $row['newsID'];
                }
                return false;
        }

        /**
         * creates a new news item for in database
         * @param <type> $authorID
         * @param <type> $authorname
         * @param <type> $title
         * @param <type> $text
         * @param <type> $options
         * @param <type> $summary
         * @param <type> $isDeleted
         * @param <type> $isDisabled
         * @param <type> $lastEditTime
         * @param <type> $editCount
         * @param <type> $editReason
         * @param <type> $deleteTime
         * @param <type> $deletedBy
         * @param <type> $deletedByID
         * @param <type> $deleteReason
         * @param <type> $ipAddress
         * @return NewsEditor the new NewsItem
         */
        static public function create($authorID, $authorname, $title,  $text, $options, $summary = null, $isDeleted = null, $isDisabled = 0, $lastEditTime = null, $editCount = 0, $editReason = null, $deleteTime = null, $deletedBy = null, $deletedByID = null, $deleteReason = null, $ipAddress = null){
                $additionalInformation = array(
                        'summary' => $summary,
                        'time' => TIME_NOW,
                        'enableHtml' => $options['enableHtml'],
                        //'enableSmilies' => $options['enableSmilies'],
                        'enableBBCodes' => $options['enableBBCodes'],
                        'ipAddress' => ($ipAddress ? $ipAddress : WCF::getSession()->ipAddress),
                        'isDisabled' => $isDisabled,
                        'editCount' => $editCount,
                        'everEnabled' => ($isDisabled ? 0 : 1)
                );
                if ($summary !== null){
                        $additionalInformation['summary'] = $summary;
                }
                $newsID = self::insert($authorID,$authorname,$title,$text,$additionalInformation);

                return new NewsEditor($newsID);
        }

        /**
         * inserts a new row in the news table
         * @param integer $authorID
         * @param string $authorname
         * @param string $title
         * @param string $text
         * @param string $additionalInformation (like summary, enableHtml, ...)
         * @return integer the ID of the new row
         */
        static public function insert($authorID,$authorname,$title,$text,$additionalInformation = array()){
                $keys =         'authorID,authorname,title,text';
                $values =       "'".escapeString($authorID)."',".
                                "'".escapeString($authorname)."',".
                                "'".escapeString($title)."',".
                                "'".escapeString($text)."'";
                foreach ($additionalInformation as $key => $value){
                        $keys .= ','.$key;
                        $values .= ",'".escapeString($value)."'";
                }
                $sql = "INSERT INTO wcf".WCF_N."_news
                        (".$keys.") VALUES (".$values.")";
                WCF::getDB()->sendQuery($sql);
                return WCF::getDB()->getInsertID();
        }





		/**
		 * updates the news item
		 * @param string $authorID the id of the "editor"
		 * @param string $authorname the name of the "editor"
		 * @param string $title
		 * @param string $text
		 * @param array $additionalInformation contains key value pairs of strings
		 */
		public function update($authorID,$authorname,$title,$text,$additionalInformation = array()){
			$sql = 	" UPDATE wcf".WCF_N."_news
					  SET authorID = '".escapeString($authorID)."',
						authorname = '".escapeString($authorname)."',
						title = '".escapeString($title)."',
						text = '".escapeString($text)."' ";
            foreach ($additionalInformation as $key => $value){
            	$sql .= ",".$key." = '".escapeString($value)."' ";
            }
            $sql .= ", editCount = editCount + 1
            		WHERE newsID = ".$this->newsID;
            WCF::getDB()->sendQuery($sql);
		}
}
?>
