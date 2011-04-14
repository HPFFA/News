<?php

require_once(WCF_DIR.'lib/data/NewsEditor.class.php');
require_once(WCF_DIR.'lib/form/MessageForm.class.php');

/**
 * NewsAddForm is responsible for adding new news entries to the system
 * it rejects duplicates and non-valid messages

 * @author logge002
 */
class NewsAddForm extends MessageForm {
        public $templateName = 'newsAdd';
        //public $useCaptcha = NEWS_CREATE_USE_CAPTCHA;
        private $minimalSummaryLength = 0;//NEWS_DEFAULT_SUMMARY_MIN_CHAR_LENTGH;
        private $maximalSummaryLength = 1000000;//NEWS_DEFAULT_SUMMARY_MAX_CHAR_LENTGH;
        private $minimalTextLength = 1;//NEWS_DEFAULT_TEXT_MIN_CHAR_LENTGH;
        private $maximalTextLength = 1000000;//NEWS_DEFAULT_TEXT_MAX_CHAR_LENTGH;

        public $newsID = null;
        public $preview, $send;
        public $title = '';
        public $summary = null;
        public $text = '';
        public $authorID = 0;
        public $authorname = '';
        public $tags = '';


        public $error = '';
        /**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
            'error' => $this->error,

			'title' => $this->title,
			'summary' => $this->summary,
			'text' => $this->text,
			'authorname' => $this->authorname,
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

		//$this->loadAvailableLanguages();

		// get max text length
		//$this->maximalTextLength = WCF::getUser()->getPermission('user.library.maxChapterLength');

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
		if (isset($_POST['send']))		$this->send			= (boolean) $_POST['send'];
        if (isset($_POST['title']))     $this->title        = MessageUtil::stripCrap(StringUtil::trim ($_POST['title']));
		if (isset($_POST['summary']))   $this->summary      = MessageUtil::stripCrap(StringUtil::trim ($_POST['summary']));
		if (isset($_POST['text']))      $this->text         = MessageUtil::stripCrap(StringUtil::trim ($_POST['text']));
		if (isset($_POST['authorname']))$this->authorname   = StringUtil::trim ($_POST['authorname']);
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
            		$this->title,
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
        $this->subject = $this->title;
		// subject, text, captcha
		parent::validate();

		// username
		$this->validateAuthorname();

		//title
		$this->validateTitle();

		//text and summary
        $this->validateText();

        $this->ensureUniqueness();

		// language
		//$this->validateLanguage();
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
            $sql = "SELECT * from wcf".WCF_N."_user
            		WHERE username = '".$this->authorname."'";
			$row = WCF::getDB()->getFirstRow($sql);
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
         * validates the title of the news
         */
	protected function validateTitle() {
		if (empty($this->title)) {
                        $this->error = 'title empty';
			throw new UserInputException('title','empty');
		}
	}

	/**
	 * @see Form::validateText()
	 */
	protected function validateText(){
		if ($this->summary){
    		$this->validateCustomText($this->minimalSummaryLength, $this->maximalSummaryLength, 'summary');
		}
        $this->validateCustomText($this->minimalTextLength, $this->maximalTextLength, 'text');
	}

    /**
	 * Validates message text.
	 */
	protected function validateCustomText($min, $max, $fieldname) {

		if (empty($this->$fieldname)) {
                        $this->error = $fieldname.' empty';
			throw new UserInputException($fieldname, 'empty');
		}

		// check text length
		if ($max !== null && StringUtil::length($this->$fieldname) > $max) {
                        $this->error = $fieldname.' tooLong';
			throw new UserInputException($fieldname, 'tooLong');
		}

		// search for censored words
		/*if (ENABLE_CENSORSHIP) {
			require_once(WCF_DIR.'lib/data/message/censorship/Censorship.class.php');
			$result = Censorship::test($this->$fieldname);
			if ($result) {
				WCF::getTPL()->assign('censoredWords', $result);
                                $this->error = $fieldname.' censored';
				throw new UserInputException($fieldname, 'censoredWordsFound');
			}
		}*/

		// check text length
		if ($min > 0 && StringUtil::length($this->$fieldname) < $min) {
                        $this->error = $fieldname.' tooShort';
			throw new UserInputException($fieldname, 'tooShort');
		}
	}

        /**
	 * prevents of duplicate news items
         */
	protected function ensureUniqueness() {
		if ($newsID = NewsEditor::test($this->title, $this->summary, $this->text)) {
			$this->error = 'news duplicate';
			throw new UserInputException('news', 'duplicate');
		}

	}

        /**
	 * @see Form::save()
	 */
	public function save() {
		// set the language temporarily to the story language
		//if ($this->languageID && $this->languageID != WCF::getLanguage()->getLanguageID()) {
		//	$this->setLanguage($this->languageID);
		//}
		parent::save();

		// parse URLs
		if ($this->parseURL == 1) {
			require_once(WCF_DIR.'lib/data/message/bbcode/URLParser.class.php');
			$this->text = URLParser::parse($this->text);
			if ($this->summary){
				$this->summary = URLParser::parse($this->summary);
			}
		}
		$this->saveOptions();

		// save story in database
        $options = $this->getOptions();
        if ($this->summary !== null){
        	$options['summary'] = $this->summary;
		}

        $this->newNews = NewsEditor::create($this->authorID, $this->authorname, $this->title, $this->text, $options);

        HeaderUtil::redirect('index.php?page=News'/*&storyID=' . $this->newStory->storyID . SID_ARG_2ND_NOT_ENCODED*/);
		// save tags
		/*if (MODULE_TAGGING && STORY_ENABLE_TAGS && $this->library->getPermission('canSetTags')) {
			$tagArray = TaggingUtil::splitString($this->tags);
			if (count($tagArray)) $this->newNews->updateTags($tagArray);
		}*/

		// reset language
		/*if ($this->userInterfaceLanguageID !== null) {
			$this->setLanguage($this->userInterfaceLanguageID, true);
		}*/
	}
}
?>
