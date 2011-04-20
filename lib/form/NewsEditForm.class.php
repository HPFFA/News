<?php

require_once(WCF_DIR.'lib/data/NewsEditor.class.php');
require_once(WCF_DIR.'lib/form/NewsForm.class.php');

/**
 * NewsEditForm is responsible for editing new news entries to the system
 * it rejects duplicates and non-valid messages

 * @author logge002
 */
class NewsEditForm extends NewsForm {
    public $templateName = 'newsEdit';
    //public $useCaptcha = NEWS_CREATE_USE_CAPTCHA;
    private $maximalEditReasonLength = 100000000;//NEWS_DEFAULT_EDITREASON_MAX_CHAR_LENTGH;

    public $editReason = '';
  
    /**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			 // attributes assigned by parent
			'editReason' => $this->editReason
			
		));
		$this->error = '';
    }

    /**
	 *
	 * @see Page::readParameters()
	 */
	public function readParameters(){
		parent::readParameters();

		if (isset($_REQUEST['newsID'])){
			$this->newsID = intval($_REQUEST['newsID']);
		} else {
			throw new IllegalLinkException();
		}
		$item = new NewsEditor($this->newsID);
		
		$this->subject = $item->subject;
		$this->authorname = $item->authorname;
		$this->summary = $item->summary;
		$this->text = $item->text;
	}

	
	/**
	 * @see Form::submit()
	 */
	public function submit() {
		parent::submit();
		EventHandler::fireAction($this, 'submit');

        if ($this->send){
            HeaderUtil::redirect('index.php?page=News');
		}
	}

	/**
	 * @see Form::validate()
	 */
	public function validate() {
		parent::validate();

        $this->validateCustomText(-1,$this->maximalEditReasonLength,'editReason');
    }
}
?>
