<?php

require_once(WCF_DIR.'lib/data/NewsEditor.class.php');
require_once(WCF_DIR.'lib/form/MessageForm.class.php');

/**
 * indicates whether the break of a standard text is after a given number of letters or words
 */
class NewsBreakType {
	const Word = 0;
	const Letter = 1;
	
	public static function getBreakType()
	{
		return NEWS_MESSAGE_BREAK_TYPE;//WCF::getUser()->getPermission('general.news.item.break_type');
	}
	
	public static function isWordType($type)
	{
		return $type == NewsBreakType::Word;	
	}
	
	public static function isLetterType($type)
	{
		return $type == NewsBreakType::Letter;	
	}
}

/**
 * NewsAddForm is responsible for adding new news entries to the system
 * it rejects duplicates and non-valid messages

 * @author logge002
 */
abstract class NewsForm extends MessageForm {
    protected $breakType = NewsBreakType::Word;
    protected $breakCount = 1000000;//NEWS_DEFAULT_SUMMARY_MAX_CHAR_LENTGH;
    protected $minimalTextLength = 1;//NEWS_DEFAULT_TEXT_MIN_CHAR_LENTGH;
    protected $maximalTextLength = 1000000;//NEWS_DEFAULT_TEXT_MAX_CHAR_LENTGH;

	public function __construct()
	{
		parent::__construct();
		$this->maximalTextLength = NEWS_MESSAGE_MAXIMAL_LENGTH;//WCF::getUser()->getPermission('general.news.item.message_maximal_length');
		$this->minimalTextLength = NEWS_MESSAGE_MINIMAL_LENGTH;//WCF::getUser()->getPermission('general.news.item.message_minimal_length');
		$this->breakCount = NEWS_MESSAGE_BREAK_COUNT;//WCF::getUser()->getPermission('general.news.item.break_count');
		$this->breakType = NewsBreakType::getBreakType();
	}

	public $preview, $send;
	public $error = '';

    public $newsID = null;
    public $subject = '';
    public $summary = null;
    public $text = '';
    public $authorID = 0;
    public $authorname = '';
    public $tags = '';

	/**
	 * throws a UserInputException
 	 */
	protected function throwUserInputException($field, $message)
	{
		$this->error = $field.' '.$message;
		throw new UserInputException($field,$message);
	}

    /**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'error' => $this->error,
		
			'newsID' => $this->newsID,
			'authorname' => $this->authorname,
			 //'subject' assigned by parent
			'summary' => $this->summary,
			 //'text' assigned by parent
			'tags' => $this->tags,
		));
		$this->error = '';
    }

    /**
	 * @see Page::show()
	 */
	public function show() {
		if( !WCF::getUser()->userID){
			//throw new PermissionDeniedException();
		} else {
			$this->authorname=WCF::getUser()->username;
		}
		// show form
		parent::show();
	}

	public function readParameters(){
		parent::readParameters();

		//if (isset($_REQUEST['newsID'])) $this->newsID = intval($_REQUEST['newsID']);
		//$this->news = new NewsEditor($this->newsID);
	}

	/**
	 * @see Page::readParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();

		if (isset($_POST['preview']))	$this->preview		= (boolean) $_POST['preview'];
		if (isset($_POST['send']))	$this->send			= (boolean) $_POST['send'];

		if (isset($_POST['newsID']))    $this->newsID       = MessageUtil::stripCrap(StringUtil::trim ($_POST['newsID']));
                //if (isset($_POST['subject'])) //assigned by parent
		if (isset($_POST['summary']))   $this->summary      = MessageUtil::stripCrap(StringUtil::trim ($_POST['summary']));
		//if (isset($_POST['text']))    //assigned by parent
		if (isset($_POST['authorname']))$this->authorname   = StringUtil::trim($_POST['authorname']);
		if (isset($_POST['tags']))      $this->tags 		= StringUtil::trim($_POST['tags']);
	}

	/**
	 * @see Form::submit()
	 */
	public function submit() {
            EventHandler::fireAction($this, 'submit');

            $this->readFormParameters();

            try {
               	if ($this->preview){
                    WCF::getTPL()->assign('preview', NewsEditor::createPreview(
            		$this->subject,
            		$this->summary,
            		$this->text,
            		$this->authorname,
            		$this->enableSmilies,
            		$this->enableHtml,
            		$this->enableBBCodes));
			}
            if ($this->send){
                $this->validate();
                $this->save();
            }
	} catch (UserInputException $e){
            $this->error = 'UserInputException '.$e;
            $this->errorField = $e->getField();
            $this->errorType = $e->getType();
	}
    }

	/**
	 * @see Form::validate()
	 */
	public function validate() {
            parent::validate();
            //text is validated through parent
            $this->validateAuthorname();
            $this->validateSummary();
            $this->ensureUniqueness();
        }

	/**
	 * Validates the username.
	 */
	protected function validateAuthorname() {
		if (empty($this->authorname)) {
        	$this->error = 'authorname empty';
			throw new UserInputException('authorname','empty');
		}
		if (WCF::getUser()->username != $this->authorname) {
			$row = WCF::getDB()->getFirstRow(
					"SELECT * from wcf".WCF_N."_user
            		 WHERE username = '".$this->authorname."'"
            );
            if (!empty ($row)){
            	$this->error = 'authorname notAvailable';
                throw new UserInputException('authorname', 'notAvailable');
           	}
            $this->authorID = 0;
		}
		else {
			$this->authorname = WCF::getUser()->username;
			$this->authorID = WCF::getUser()->userID;
		}
	}

	/**
	 * validates the subject of the news
	 */
	protected function validateSubject() {
		if (empty($this->subject))
            $this->throwUserInputException('subject', 'empty');
	}
	
        
        protected function validateTextField($fieldname, $minimalTextLenght, $maximalTextLenght){
            $this->throwErrorWhenEmpty($fieldname);
            
            if ($maximalTextLenght !== null
                    && StringUtil::length($this->$fieldname) > $maximalTextLenght){
                $this->error = $fieldname.' tooLong';
                throw new UserInputException($fieldname, 'tooLong');
            }
            
            if ($minimalTextLenght !== null
                    && StringUtil::length($this->$fieldname) <= $minimalTextLenght){
                $this->error = $fieldname.' tooShort';
                throw new \UserInputException($fieldname, 'tooShort');
            }
                
        }
        
	/**
	 * @see Form::validateText()
	 */
	protected function validateText(){
            /*$this->throwErrorWhenEmpty('text');
        
            if ($this->maximalTextLength !== null 
		&& StringUtil::length($this->text) > $this->maximalTextLength) {
                $this->error = 'text tooLong';
                throw new UserInputException('text', 'tooLong');
            }
		
            // check text length
            if ($this->minimalTextLength > 0 
		&& StringUtil::length($this->text) < $this->minimalTextLength) {
                $this->error = 'text tooShort';
                throw new UserInputException('text', 'tooShort');
            }*/
         $this->validateTextField('text',  $this->minimalTextLength, $this->maximalTextLength);
	}
	
	/**
	 * validates the summary
	 */
	protected function validateSummary(){
		
            if (!empty($this->summary)){
                $this->throwErrorWhenEmpty('summary');
            }
            if (NewsBreakType::isWordType($this->breakType)){
		if ($this->breakCount !== null 
                        && count(explode(' ',$this->summary)) > $this->breakCount) {
                    $this->error = 'summary tooLong';
                    throw new UserInputException('summary', 'tooLong');
		}
            } 
			
            if (NewsBreakType::isLetterType($this->breakType)){
                if ($this->breakCount !== null 
			&& StringUtil::length($this->summary) > $this->breakCount) {
                    $this->error = 'summary tooLong';
                    throw new UserInputException('summary', 'tooLong');
		}
            }		
	}
	
	private function throwErrorWhenEmpty($fieldname)
	{
		if (empty($this->$fieldname)){
			$this->error = $fieldname.' empty';
			throw new UserInputException($fieldname, 'empty');
		}
	}

		
	/**
	 * prevents of duplicate news items
     */
	protected function ensureUniqueness() {
		if ($newsID = NewsEditor::test($this->subject, $this->summary, $this->text)) {
			$this->error = 'news duplicate';
			throw new UserInputException('news', 'duplicate');
		}
	}

    /**
	 * @see MessageForm::getOptions()
	 * @return	array
	 */
	protected function getOptions() {
		return array(
			'enableSmilies' => $this->enableSmilies,
			'enableHtml' => $this->enableHtml,
			'enableBBCodes' => $this->enableBBCodes,
			//'showSignature' => $this->showSignature,
		);
	}
        /**
	 * @see Form::save()
	 */
	public function save() {
		parent::save();
		// parse URLs
		if ($this->parseURL == 1) {
			require_once(WCF_DIR.'lib/data/message/bbcode/URLParser.class.php');
			$this->text = URLParser::parse($this->text);
			if ($this->summary){
				$this->summary = URLParser::parse($this->summary);
			}
		}
		// save story in database
        $options = $this->getOptions();
        $news = NewsEditor::create(
        	$this->authorID, 
        	$this->authorname, 
        	$this->subject, 
        	$this->text, 
        	$this->summary, 
        	$this->getOptions());

        HeaderUtil::redirect('index.php?page=News');
	}
}
?>
