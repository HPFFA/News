<?php

require_once(WCF_DIR.'lib/form/NewsForm.class.php');

/**
 * NewsAddForm is responsible for adding new news entries to the system
 * it rejects duplicates and non-valid messages

 * @author logge002
 */
class NewsAddForm extends NewsForm {
    public $templateName = 'newsAdd';
}
?>
