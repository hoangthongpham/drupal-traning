<?php
    namespace Drupal\module_manage_article\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\node\Entity\Node;
    use Drupal\taxonomy\Entity\Term;
    use Drupal\menu_link_content\Entity\MenuLinkContent;
    
    class CreditCardForm extends FormBase {
        /**
         * {@inheritdoc}
         */
        
        public function getFormId(){
            return 'credit_card';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form, FormStateInterface $form_state) {
            $form['entity_id'] = [
                '#type' => 'textfield',
                '#title' => $this->t('entity_id'),
                '#default_value' => '',
                '#required' => true,
                '#ajax' => [
                    'callback' => '::loadOrderDataAjax',
                    'event' => 'change',
                    'progress' => [
                        'type' => 'throbber',
                        'message' => $this->t('Loading data...'),
                    ],
                ],
            ];
        
            $form['other_fields'] = [
                '#type' => 'container',
                '#attributes' => ['id' => 'other-fields-container'],
            ];
        
            $form['other_fields']['langcode'] = [
                '#type' => 'textfield',
                '#title' => $this->t('langcode'),
                '#default_value' => '',
                '#required' => true,
                '#attributes' => ['id' => 'edit-other-fields-langcode'],
            ];
        
            $form['other_fields']['body_value'] = [
                '#type' => 'text_format',
                '#title' => $this->t('Body'),
                '#default_value' => '',
                '#required' => true,
                '#attributes' => ['id' => 'edit-other-fields-body-value'],
            ];
        
            $form['other_fields']['field_tags'] = [
                '#type' => 'textfield',
                '#title' => $this->t('Tags'),
                '#default_value' => '',
                '#description' => $this->t('Enter a comma-separated list...'),
                '#attributes' => ['id' => 'edit-other-fields-field-tags'],
            ];
        
            $form['other_fields']['status'] = [
                '#type' => 'checkbox',
                '#title' => $this->t('Published'),
                '#default_value' => 1,
                '#attributes' => ['id' => 'edit-other-fields-status'],
            ];
        
            $form['save'] = array(
                '#type' => 'submit',
                '#value' => 'Save',
                '#button_type' => 'primary',
            );
        
            return $form;
        }
        

        public function loadOrderDataAjax(array &$form, FormStateInterface $form_state) {
            return $form['other_fields'];
        }
        /**
         * {@inheritdoc}
         */
        public function validateForm(array &$form, FormStateInterface $form_state){
            $title =$form_state->getValue('title');
            if(trim($title) == ''){
                $form_state->setErrorByName('title',$this->t('Title field is required'));
            }
            elseif($form_state->getValue('body_value') ==''){
                $form_state->setErrorByName('body_value',$this->t('Body field is required'));
            }
        }
        /**
         * {@inheritdoc}
         */
        public function submitForm (array &$form, FormStateInterface $form_state ){
            $postData = $form_state->getValues();
            if($postData['field_image']){
                $fileId = $postData['field_image'][0]; 
            }else{
                $fileId=null;
            }
            if($postData['field_tags']){
                $new_term = Term::create([
                    'vid' => 'tags',
                    'name' => $postData['field_tags'],
                ]);
                $new_term->enforceIsNew();
                $new_term->save();
                $node = Node::create(array(
                    'type' => 'article',
                    'title' => $postData['title'],
                    'langcode' => 'en', 
                    'body' => $postData['body_value'],
                    'field_image'=>[
                        'target_id' => $fileId,
                        'alt'=>'',
                        'title'=>''
                    ],
                    'field_tags'=>[
                        'target_id'=>$new_term->tid->value
                    ],
                    'status'=>$postData['status'], 
                ));  
            }else{
                $node = Node::create(array(
                    'type' => 'article',
                    'title' => $postData['title'],
                    'langcode' => 'en',
                    'body' => $postData['body_value'],
                    'field_image'=>[
                        'target_id' => $fileId,
                        'alt'=>'',
                        'title'=>''
                    ],
                    'status'=>$postData['status'], 
                ));  
            }    
            $node->save();
            $response  = new \Symfony\Component\HttpFoundation\RedirectResponse('/admin/articles');
            $response->send();
            \Drupal::messenger()->addStatus(t('Article data save successfully!'), 'status',TRUE);

        }

    }

