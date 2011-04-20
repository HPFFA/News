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
         * @param <type> $subject
         * @param <type> $summary
         * @param <type> $text
         * @param <type> $authorname
         * @param <type> $enableSmilies
         * @param <type> $enableHtml
         * @param <type> $enableBBCodes
         * @return News
         */
        static public function createPreview($subject, $summary, $text, $authorname ,$enableSmilies = 1, $enableHtml = 0, $enableBBCodes= 1) {

            $news = new News(null, array(
                'newsID' 		=> 0,
                'subject' 		=> $subject,
                'summary' 		=> $summary,
                'text' 			=> $text,
                'authorname' 	=> $authorname,
                'enableSmilies' => $enableSmilies,
                'enableHtml' 	=> $enableHtml,
                'enableBBCodes' => $enableBBCodes));
            return $news;
        }

        /**
         * looks for a news item in the data base with the given content</br>
         * a item is treated as identical when
		 *  - subject and text equals
		 *  - subject and summary equals
         * @param <string> $subject
         * @param <string> $summary
         * @param <string> $text
         * @param <integer> $newsID default 0
         */
        static public function test($subject, $summary, $text, $newsID = 0){
                $selectNewsID = "SELECT newsID FROM wcf".WCF_N."_news ";
                $row = WCF::getDB()->getFirstRow(
                	$selectNewsID." WHERE subject = '".$subject."' and text = '".$text."'");
                if (!empty($row['newsID'])){
                        return $row['newsID'];
                }
                $row = WCF::getDB()->getFirstRow(
                	$selectNewsID." WHERE subject = '".$subject."' and summary = '".$summary."'");
                if (!empty($row['newsID'])){
                        return $row['newsID'];
                }
                return false;
        }

        /**
         * creates a new news item for in database
         * @param <type> $authorID
         * @param <type> $authorname
         * @param <type> $subject
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
        static public function create($authorID, $authorname, $subject,  $text, $summary = "", $options, $isDeleted = null, $isDisabled = 0, $lastEditTime = null, $editCount = 0, $editReason = null, $deleteTime = null, $deletedBy = null, $deletedByID = null, $deleteReason = null, $ipAddress = null){
        		
                $additionalInformation = array(
                        'time' 			=> TIME_NOW,
                        'enableHtml' 	=> $options['enableHtml'],
                        //'enableSmilies' => $options['enableSmilies'],
                        'enableBBCodes' => $options['enableBBCodes'],
                        'ipAddress' 	=> ($ipAddress ? $ipAddress : WCF::getSession()->ipAddress),
                        'isDisabled' 	=> $isDisabled,
                        'editCount' 	=> $editCount,
                        'everEnabled' 	=> ($isDisabled ? 0 : 1)
                );
				
                $newsID = self::insert($authorID,$authorname,$subject,$text,$summary,$additionalInformation);

                return new NewsEditor($newsID);
        }

        /**
         * inserts a new row in the news table
         * @param integer $authorID
         * @param string $authorname
         * @param string $subject
         * @param string $text
         * @param string $additionalInformation (like summary, enableHtml, ...)
         * @return integer the ID of the new row
         */
        static public function insert($authorID,$authorname,$subject,$text,$summary,$additionalInformation = array()){
                $keys =         'authorID, authorname, subject, text, summary';
                $values =       "'".escapeString($authorID)."', 
                  			     '".escapeString($authorname)."', 
								 '".escapeString($subject)."', 
								 '".escapeString($text)."', 
								 '".escapeString($summary)."'";
                foreach ($additionalInformation as $key => $value){
                        $keys .= ', '.$key;
                        $values .= ", '".escapeString($value)."'";
                }
                WCF::getDB()->sendQuery(
                		"INSERT INTO wcf".WCF_N."_news
                        (".$keys.") 
                        VALUES 
                        (".$values.")"
                );
                return WCF::getDB()->getInsertID();
        }





		/**
		 * updates the news item
		 * @param string $authorID the id of the "editor"
		 * @param string $authorname the name of the "editor"
		 * @param string $subject
		 * @param string $text
		 * @param string summary
		 * @param array $additionalInformation contains key value pairs of strings
		 */
		public function update($authorID,$authorname,$subject,$text,$summary,$additionalInformation = array()){
			$update =	"UPDATE wcf".WCF_N."_news
					  	 SET authorID 		= '".escapeString($authorID)."',
						 	 authorname 	= '".escapeString($authorname)."',
						 	 subject 		= '".escapeString($subject)."',
						 	 text 			= '".escapeString($text)."',
						 	 summary		= '".escapeString($summary)."'";
            foreach ($additionalInformation as $key => $value){
            	$update .= ", ".$key." = '".escapeString($value)."' ";
            }
            $update .= ", editCount = editCount + 1
            			WHERE newsID = ".$this->newsID;
            WCF::getDB()->sendQuery($update);
		}
}
?>
