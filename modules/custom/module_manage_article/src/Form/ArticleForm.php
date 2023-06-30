<?php
    namespace Drupal\module_manage_article\Form;
    use Drupal\Core\Form\FormBase;
    use Drupal\Core\Form\FormStateInterface;
    use Drupal\node\Entity\Node;
    use Drupal\taxonomy\Entity\Term;
    use Drupal\menu_link_content\Entity\MenuLinkContent;
    
    class ArticleForm extends FormBase {
        /**
         * {@inheritdoc}
         */
        
        public function getFormId(){
            return 'add_article';
        }

        /**
         * {@inheritdoc}
         */
        public function buildForm(array $form,FormStateInterface $form_state ){
            $form['title'] = array(
                '#type'=>'textfield',
                '#title'=>$this->t('Title'),
                '#default_value'=>'',
                '#required' => true,
            );
            $folder = date('Y-m', time());
            $form['image'] = array(
                '#type' => 'details',
                '#title' =>$this->t('Image'),
                '#open' => TRUE,
            );
            $form['image']['field_image'] = [
                '#type' => 'managed_file',
                '#title' => $this->t('Add a new file'),
                '#upload_validators' => [
                    'file_validate_extensions' => ['gif png jpg jpeg'],
                    'file_validate_size' => [25600000],
                ],
                '#upload_location' => 'public://'.$folder.'',
            ];

            $form['body_value'] = array(
                '#type'=>'text_format',
                '#title'=>$this->t('Body'),
                '#default_value'=>'',
                '#required' => true,
            );
            $form['field_tags'] = array(
                '#type'=>'textfield',
                '#title'=>$this->t('Tags'),
                '#default_value'=>'',
                '#description'=>$this->t('Enter a comma-separated list. For example: Amsterdam, Mexico City, "Cleveland, Ohio" '),
            );
            $form['status'] = array(
                '#type'=>'checkbox',
                '#title' =>$this->t('Published'),
                '#default_value'=>1,
            );

            $form['save'] = array(
                '#type'=>'submit',
                '#value'=>'Save',
                '#button_type'=> 'primary'
            );  
            return $form;

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
                    'uid' => '1',
                    'status' => 1,
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
                    'uid' => '1',
                    'status' => 1,
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

