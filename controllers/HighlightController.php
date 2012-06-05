<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

class SolrSearch_HighlightController extends Omeka_Controller_Action
{
    public function indexAction()
    {
        $form = $this->highlightForm();
        $this->view->form = $form;
    }

    public function updateAction()
    {
        $form = $this->highlightForm();

        if ($_POST) {
            if ($form->isValid($this->_request->getPost())) {
                //get posted values		
                $uploadedData = $form->getValues();
                set_option('solr_search_hl', $uploadedData['solr_search_hl']);
                set_option('solr_search_snippets', $uploadedData['solr_search_snippets']);
                set_option('solr_search_fragsize', $uploadedData['solr_search_fragsize']);
                $this->flashSuccess('Hit highlighting features modified.');
            } else {
                $this->flashError('Failed to gather posted data.');
                $this->view->form = $form;
            }
        }	
    }

    private function highlightForm(){
        include "Zend/Form/Element.php";
        $form = new Zend_Form();
        $form->setAction('update');
        $form->setMethod('post');
        $form->setAttrib('enctype', 'multipart/form-data');

        //set true or false
        $hl = new Zend_Form_Element_Select('solr_search_hl');
        $hl->setLabel('Highlighting:');
        $hl->setDescription('Enable/Disable highlighting matches in Solr fields');
        $hl->addMultiOption('true', 'True');
        $hl->addMultiOption('false', 'False'); 
        $hl->setValue(get_option('solr_search_hl'));
        $form->addElement($hl);

        //number of snippets
        $snippets = new Zend_Form_Element_Text('solr_search_snippets');
        $snippets->setLabel('Snippets:');
        $snippets->setDescription('The maximum number of highlighted snippets to generate');
        $snippets->setValue(get_option('solr_search_snippets'));
        $snippets->setRequired('true');    
        $snippets->addValidator(new Zend_Validate_Int());
        $form->addElement($snippets);

        //fragment size
        $fragsize = new Zend_Form_Element_Text('solr_search_fragsize');
        $fragsize->setLabel('Snippet Size:');
        $fragsize->setDescription('The maximum number of characters to display in a snippet');
        $fragsize->setValue(get_option('solr_search_fragsize'));
        $fragsize->setRequired('true');
        $fragsize->addValidator(new Zend_Validate_Int());
        $form->addElement($fragsize);

        //Submit button
        $form->addElement('submit', 'submit');
        $submitElement=$form->getElement('submit');
        $submitElement->setLabel('Submit');
        return $form;
    }
}


