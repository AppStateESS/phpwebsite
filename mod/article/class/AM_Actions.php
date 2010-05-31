<?php
/**
* This is the PHPWS_AM_Actions class.
* It contains functions to apply bulk actions to articles.
*
* @version $Id: AM_Actions.php,v 1.16 2009/01/19 16:44:02 adarkling Exp $
*
* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
* @module Article Manager
*/
class PHPWS_AM_Actions
{
	/**
	* Sets the flag to display this article id on the home page.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param none
	* @return none
	*/
	function set_main_article ($id, $status)
	{
		if(!$id)
			return;
		$now = time();
        $db = new PHPWS_DB('article');
        $db->addValue('mainarticle', (int) (bool) $status);
        $db->addWhere('id', (int) $id);
        if ($status) {
	        $db->addWhere('publication_date', $now, '<=');
	        $db->addWhere('expiration_date', $now, '>', 'and', 'exp');
	        $db->addWhere('expiration_date', '0', '=', 'or', 'exp');
        }
        $result = $db->update();
        if (!$result || PHPWS_Error::logIfError($result))
        	return false;
        return true;
	}

	/**
	* Clones an article for use in translations.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param int id : Id number of the article to be cloned
	* @param string language : Language of the new article
	* @param string prefix : Text to insert at the start of each text section
	* @return object : New article object, ready to be saved.
	*/
	function clone_article ($id, $prefix='', $base_language='en', $language='en')
	{
		if (!Current_User::allow('article', 'create_articles'))
			return;

		/* Load the article to be cloned */
		$new_article = new PHPWS_Article((int) $id, null, $base_language);
		/* Set all applicable variables to "new article" values */
		//$new_article->is_new_article = true;

		if (Current_User::getDisplayName()) {
			$new_article->created_username = $new_article->updated_username = Current_User::getDisplayName();
			$new_article->created_id = $new_article->updated_id = Current_User::getId();
		}
		else {
			$new_article->created_username = $new_article->updated_username = '';
			$new_article->created_id = $new_article->updated_id = '';
		}
		$new_article->created_date = date("Y-m-d H:i:s");
		$new_article->updated_date = date("Y-m-d H:i:s");
		$new_article->hits = 0;
		$new_article->approved = (!PHPWS_Settings::get('article', 'need_approval') || Current_User::allow('article', 'approval')) ?1:0;
		$new_article->version = -1;
		$new_article->language = $language;
		$new_article->is_base_article = 0;

		/* Append prefix text to title & summary */
		$new_article->title = $prefix.$new_article->title;
		$new_article->summary = $prefix.$new_article->summary;

		/* Loop through all sections */
		foreach($new_article->order as $value) {
			/* Set all applicable variables to "new section" values */
		    $new_article->sections[$value]->_edited = 1;
		    $new_article->sections[$value]->language = $language;
		    $new_article->sections[$value]->title = $prefix.'  '.$new_article->sections[$value]->title;
		    $new_article->sections[$value]->text = $prefix.'  '.$new_article->sections[$value]->text;
		}
		return $new_article;
	}

	/**
	* Deletes all inactive articles.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param none
	* @return none
	*/
	function purge_articles ()
	{
		$db = new PHPWS_DB('article');
		$db->addColumn('id');
		$db->addColumn('key_id');
		$db->addWhere('active', 1);
		$result = $db->select();
		if (empty($result) || PHPWS_Error::logIfError($result) || !Current_User::isUnrestricted('article'))
			return;

		$ids = $keys = array();
		foreach ($result AS $row) {
			$ids[] = $row['id'];
			$keys[] = $row['key_id'];
		}

		/* Delete all inactive articles */
		$db->delete();

		/* Delete all associated versions */
		$db->reset();
		$db = new PHPWS_DB('article_version');
		$db->addWhere('id', $ids, 'IN');
		$db->delete();

		/* Delete all associated keys */
		foreach ($keys AS $key) {
			Key::drop($key);
		}
	}

	/**
	* Article Import Dialog.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param none.
	* @return string : HTML display
	*/
    function import_articles () {
    	if (isset($_REQUEST['xmlfile']))
		{
			$overwrite = (isset($_REQUEST['overwrite'])) ?1 : 0;
			$convert_http = (isset($_REQUEST['convert_http'])) ?1 : 0;
			$files = array();
			foreach ($_REQUEST['xmlfile'] AS $file)
				$files[]['FILE'] = PHPWS_AM_Actions::XML_import($file,'files/article/', $overwrite, $convert_http);

			$template['RESULT'] = dgettext('article', 'The following files have been imported');
			if ($convert_http)
				$template['CONVERTED'] = ('All Local links have been converted');
			$template['file_list'] = $files;
			return PHPWS_Template::processTemplate($template,'article','import_result.tpl');
		}
		else
    	{
			$db = new PHPWS_DB('article_seq');
			$db->addColumn('id');
			$max_id = $db->select('one');
			if (PHPWS_Error::logIfError($max_id))
				$max_id = 0;
			$form = new PHPWS_Form('am_import');
			$form->addhidden('module', 'article');
			$form->addCheckBox('overwrite');
			$form->setMatch('overwrite', 1);
			$form->setLabel('overwrite', sprintf(dgettext('article', 'Overwrite old article data when ids match')));

			$form->addCheckBox('convert_http');
			$form->setMatch('convert_http', 1);
			$form->setLabel('convert_http', sprintf(dgettext('article', 'Convert all local links to the new base address "%s"'), PHPWS_Core::getHomeHttp()));

			$form->addTplTag('INSTRUCTIONS', dgettext('article', 'Article content can be imported from specially-structured XML files located in the /files/article directory.  Simply select the file(s) that you want to import and press the [Import] button.'));
			$form->addTplTag('NUMBERING', dgettext('article', 'Article numbers will be assigned depending on the id number included in the file.  If there is no id, it will assigned the next available one.'));
			$form->addTplTag('AVAILABLE_ID', sprintf(dgettext('article', 'The next available article id is %s.  If you import an article with an id greater than this, the next available id will be increased to that id plus one.'), ++$max_id));
			$form->addTplTag('SETTINGS_TITLE', dgettext('article', 'Import Options'));
			$form->addTplTag('LIST_TITLE', dgettext('article', 'Files to be Imported'));
			$form->addTplTag('BTN_IMPORT', '<input name="ARTICLE_vars[op:import]" value="'.dgettext('article', 'Import').'" type="submit">');

			/* Retrieve all .xml files */
			$content = PHPWS_File::readDirectory(PHPWS_HOME_DIR . 'files/article/' , 0, 1, 0, array('xml','XML'));
			$template = $form->getTemplate();
			$files = array();
			if (!empty($content))
				foreach ($content AS $value) {
					$element = 'xmlfile[]';
					$files[]['FILE'] = '<input name="'.$element.'" id="am_import_'.$element.'" title="'.$value.'" value="'.$value.'" _base_href="http://localhost/fallout/" type="checkbox"> <label class="checkbox-label" for="am_import_'.$element.'">'.$value."</label>\n";
				}
			else
				$files[]['FILE'] = dgettext('article', 'None');
			$template['file_list'] = $files;
			return PHPWS_Template::processTemplate($template,'article','import.tpl');
		}
    }


	/**
	* Exports an article to XML format.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param int $id : id# of the article to export.
	* @return string : XML Stream
	*/
    public static function XML_export ($id) {
    	/* Load the full article & set the base http */
    	$article = new PHPWS_Article($id);
    	$article->_base_http = PHPWS_Core::getHomeHttp();

		require_once(PHPWS_SOURCE_DIR . 'lib/pear/XML/Serializer.php');
		$options = array(
		    'indent'          => "\t",
		    'defaultTagName' => 'array_item',
		    'typeHints'       => true
		);
		$serializer = new XML_Serializer($options);
		$serializer->setErrorHandling(PEAR_ERROR_DIE);
		$serializer->serialize($article);
		$xml = str_replace(array(' _type="string"',' _type="integer"'), '', $serializer->getSerializedData());
        return PHPWS_File::writeFile(PHPWS_HOME_DIR.'files/article/'.dgettext('article', 'Article').'_'.$article->id.'.xml', $xml, true, true);
    }


	/**
	* Imports articles to XML format.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @param int $id : id# of the article to export.
	* @return string : XML Stream
	*/
    public static function XML_import ($file, $directory, $overwrite, $convert_http) {
		// Instantiate the unserializer
		require_once(PHPWS_SOURCE_DIR . 'lib/pear/XML/Unserializer.php');
		$unserializer = new XML_unserializer();
		$autodetect = true;
		$overwritten = false;
		/* If the file exists, load it. */
		$filename = $directory . $file;
		if (!$xml = @PHPWS_File::readFile(PHPWS_HOME_DIR . $filename))
            //file not found -- look in source dir
            if (!$xml = @PHPWS_File::readFile(PHPWS_SOURCE_DIR . $filename))
                return sprintf(dgettext('article', '"%s" could not be read.'), $filename);

		/* Unserialize the data structure */
		$status = $unserializer->unserialize($xml);
		/* Check whether serialization worked */
		if (PHPWS_Error::logIfError($status))
			return sprintf(dgettext('article', '"%1$s" could not be imported.  %2$s'), $file, $status->getMessage());

		/* Create import object.  If no id was in the XML, id will be null */
		$article = $unserializer->getunserializedData();
		$article->version = $article->approved = 1;
		$article->key_id = 0;
		// If an id was specified...
		if ($article->id) {
			// If the id already exists and we can overwrite...
			$oldarticle = new PHPWS_Article($article->id);
			if ($oldarticle->id)
			{
				if ($overwrite) {
					/* transfer tracking variables */
					$article->version = $oldarticle->version + 1;
					$article->key_id = $oldarticle->key_id;
					/* set the status flag */
					$overwritten = true;
				}
				else
					return sprintf(dgettext('article', 'ERROR: %s cannot be imported.  An article with this id already exists!'), $file);
			}
			/* ...otherwise, we're inserting a new article @ that location */
			else
				$autodetect = false;
		}

		/* If requested, convert links */
		if ($convert_http && !empty($article->_base_http))
			foreach($article->pages AS $pagenum => $page)
				foreach($page['section'] AS $sectnum => $section)
					str_replace($article->_base_http, PHPWS_Core::getHomeHttp(), $article->pages[$pagenum]['section'][$sectnum]['text']);

		/* Save the imported object */
		$article->save($autodetect);
		$article_link = PHPWS_Text::moduleLink(sprintf(dgettext('article', 'article #%s'), $article->id), 'article', array('id' => $article->id));

		/* Update next available id in the sequence table */
		if (!$autodetect) {
			$db = new PHPWS_DB('article');
			$db->updateSequenceTable();
		}

		if ($overwrite && $overwritten)
			return sprintf(dgettext('article', '%1$s has overwritten %2$s'), $file, $article_link);
		else
			return sprintf(dgettext('article', '%1$s has been added as %2$s'), $file, $article_link);
    }


	/**
	* Sends an email.
	*
	* @author Eloi George <eloi@NOSPAM.bygeorgeware.com>
	* @return bool : Success or Failiure
	*/
    function sendmail ($subject, $sender_name, $sender_email, $recipient_email, $txt_body, $html_body = '', $CC = true) {
		$headers['From'] = '"' . $sender_name . '" <'.$sender_email.'>';
		$headers['Subject'] = $subject;
		$headers['Reply-To'] = $headers['From'];
		if ($CC)
			$headers['Cc'] = $headers['From'];
		$headers['X-Priority'] = 1;
		$headers['X-MSmail-Priority'] = 'High';
		$headers['X-Mailer'] = 'PHP ' . phpversion();

		/* PEAR mail class */
		require_once(PHPWS_SOURCE_DIR . 'lib/pear/Mail.php');
		require_once(PHPWS_SOURCE_DIR . 'lib/pear/Mail/mime.php');
		$mime = new Mail_mime("\r\n");
		$mime->setTXTBody($txt_body);
		if (!empty($html_body))
			$mime->setHTMLBody($html_body);
		$body = $mime->get();
		$hdrs = $mime->headers($headers);
		$mail_object =& Mail::factory('mail');
		return $mail_object->send($recipient_email, $hdrs, $body);
    }

}
?>